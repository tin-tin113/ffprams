<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Allocation;
use App\Models\Barangay;
use App\Models\Beneficiary;
use App\Models\DirectAssistance;
use App\Models\DistributionEvent;
use App\Models\ProgramName;
use App\Models\ResourceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmsAutomationPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('sms.send_on_event_ongoing');
        Cache::forget('sms.send_on_direct_assistance_status_change');

        config()->set('services.sms.api_key', 'test-api-key');
        config()->set('services.sms.api_url', 'https://example.test/sms');

        Http::fake([
            '*' => Http::response(['success' => true], 200),
        ]);
    }

    public function test_event_transition_to_ongoing_sends_sms_when_enabled(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $fixtures = $this->makeFixtures($admin);

        $event = DistributionEvent::create([
            'barangay_id' => $fixtures['barangay']->id,
            'resource_type_id' => $fixtures['resourceType']->id,
            'program_name_id' => $fixtures['program']->id,
            'distribution_date' => now()->toDateString(),
            'status' => 'Pending',
            'beneficiary_list_approved_at' => now(),
            'beneficiary_list_approved_by' => $admin->id,
            'created_by' => $admin->id,
            'type' => 'physical',
            'total_fund_amount' => null,
        ]);

        Allocation::create([
            'distribution_event_id' => $event->id,
            'release_method' => 'event',
            'beneficiary_id' => $fixtures['beneficiary']->id,
            'program_name_id' => $fixtures['program']->id,
            'resource_type_id' => $fixtures['resourceType']->id,
            'quantity' => 4,
            'amount' => null,
            'remarks' => 'Automation test allocation',
        ]);

        Cache::forever('sms.send_on_event_ongoing', true);

        $response = $this->actingAs($admin)->post(route('distribution-events.updateStatus', $event), [
            'status' => 'Ongoing',
        ]);

        $response->assertRedirect();

        $event->refresh();
        $this->assertSame('Ongoing', $event->status);

        $this->assertDatabaseHas('sms_logs', [
            'beneficiary_id' => $fixtures['beneficiary']->id,
            'status' => 'sent',
        ]);
    }

    public function test_sms_automation_settings_route_updates_cache_values(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($user)->post(route('sms.settings.automation'), [
            'send_on_event_ongoing' => '1',
        ]);

        $response->assertRedirect(route('sms.index'));

        $this->assertTrue((bool) Cache::get('sms.send_on_event_ongoing'));
        $this->assertFalse((bool) Cache::get('sms.send_on_direct_assistance_status_change'));
    }

    public function test_event_transition_to_ongoing_does_not_send_sms_when_disabled(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $fixtures = $this->makeFixtures($admin);

        $event = DistributionEvent::create([
            'barangay_id' => $fixtures['barangay']->id,
            'resource_type_id' => $fixtures['resourceType']->id,
            'program_name_id' => $fixtures['program']->id,
            'distribution_date' => now()->toDateString(),
            'status' => 'Pending',
            'beneficiary_list_approved_at' => now(),
            'beneficiary_list_approved_by' => $admin->id,
            'created_by' => $admin->id,
            'type' => 'physical',
            'total_fund_amount' => null,
        ]);

        Allocation::create([
            'distribution_event_id' => $event->id,
            'release_method' => 'event',
            'beneficiary_id' => $fixtures['beneficiary']->id,
            'program_name_id' => $fixtures['program']->id,
            'resource_type_id' => $fixtures['resourceType']->id,
            'quantity' => 4,
            'amount' => null,
            'remarks' => 'Automation test allocation',
        ]);

        Cache::forever('sms.send_on_event_ongoing', false);

        $response = $this->actingAs($admin)->post(route('distribution-events.updateStatus', $event), [
            'status' => 'Ongoing',
        ]);

        $response->assertRedirect();

        $event->refresh();
        $this->assertSame('Ongoing', $event->status);
        $this->assertDatabaseCount('sms_logs', 0);
    }

    public function test_direct_assistance_mark_ready_for_release_sends_sms_when_enabled(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $fixtures = $this->makeFixtures($admin);

        $record = DirectAssistance::create([
            'beneficiary_id' => $fixtures['beneficiary']->id,
            'program_name_id' => $fixtures['program']->id,
            'resource_type_id' => $fixtures['resourceType']->id,
            'quantity' => 3,
            'amount' => null,
            'remarks' => 'Pending distribution',
            'created_by' => $admin->id,
            'status' => 'planned',
        ]);

        Cache::forever('sms.send_on_direct_assistance_status_change', true);

        $response = $this->actingAs($admin)->post(route('direct-assistance.mark-ready-for-release', $record));

        $response->assertRedirect();

        $record->refresh();
        $this->assertSame('ready_for_release', $record->status);

        $this->assertDatabaseHas('sms_logs', [
            'beneficiary_id' => $fixtures['beneficiary']->id,
            'status' => 'sent',
        ]);
    }

    public function test_direct_assistance_store_does_not_send_automatic_sms_on_record_creation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $fixtures = $this->makeFixtures($admin);

        Cache::forever('sms.send_on_direct_assistance_status_change', true);

        $response = $this->actingAs($admin)->post(route('direct-assistance.store'), [
            'beneficiary_id' => $fixtures['beneficiary']->id,
            'program_name_id' => $fixtures['program']->id,
            'resource_type_id' => $fixtures['resourceType']->id,
            'quantity' => 5,
            'remarks' => 'Created with no status-change yet.',
        ]);

        $response->assertRedirect(route('direct-assistance.index'));

        $record = DirectAssistance::query()->latest('id')->firstOrFail();
        $this->assertSame('planned', $record->status);
        $this->assertDatabaseCount('sms_logs', 0);
    }

    public function test_direct_assistance_mark_not_received_does_not_send_sms(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $fixtures = $this->makeFixtures($admin);

        $record = DirectAssistance::create([
            'beneficiary_id' => $fixtures['beneficiary']->id,
            'program_name_id' => $fixtures['program']->id,
            'resource_type_id' => $fixtures['resourceType']->id,
            'quantity' => 3,
            'amount' => null,
            'remarks' => 'Pending distribution',
            'created_by' => $admin->id,
            'status' => 'ready_for_release',
        ]);

        Cache::forever('sms.send_on_direct_assistance_status_change', true);

        $response = $this->actingAs($admin)->post(route('direct-assistance.mark-not-received', $record));

        $response->assertRedirect();

        $record->refresh();
        $this->assertSame('not_received', $record->status);

        $this->assertDatabaseCount('sms_logs', 0);
    }

    public function test_direct_assistance_status_change_does_not_send_sms_when_disabled(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $fixtures = $this->makeFixtures($admin);

        $record = DirectAssistance::create([
            'beneficiary_id' => $fixtures['beneficiary']->id,
            'program_name_id' => $fixtures['program']->id,
            'resource_type_id' => $fixtures['resourceType']->id,
            'quantity' => 3,
            'amount' => null,
            'remarks' => 'Pending distribution',
            'created_by' => $admin->id,
            'status' => 'planned',
        ]);

        Cache::forever('sms.send_on_direct_assistance_status_change', false);

        $response = $this->actingAs($admin)->post(route('direct-assistance.mark-ready-for-release', $record));

        $response->assertRedirect();

        $record->refresh();
        $this->assertSame('ready_for_release', $record->status);
        $this->assertDatabaseCount('sms_logs', 0);
    }

    public function test_direct_allocation_mark_ready_for_release_sends_sms_when_enabled(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $fixtures = $this->makeFixtures($admin);

        $allocation = $this->makeDirectAllocationFixture($admin, $fixtures);

        Cache::forever('sms.send_on_direct_assistance_status_change', true);

        $response = $this->actingAs($admin)->post(route('allocations.mark-ready-for-release', $allocation));

        $response->assertRedirect();

        $allocation->refresh();
        $this->assertSame('ready_for_release', $allocation->release_status);

        $this->assertDatabaseHas('sms_logs', [
            'beneficiary_id' => $fixtures['beneficiary']->id,
            'status' => 'sent',
        ]);
    }

    public function test_direct_allocation_mark_ready_for_release_does_not_send_sms_when_disabled(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $fixtures = $this->makeFixtures($admin);

        $allocation = $this->makeDirectAllocationFixture($admin, $fixtures);

        Cache::forever('sms.send_on_direct_assistance_status_change', false);

        $response = $this->actingAs($admin)->post(route('allocations.mark-ready-for-release', $allocation));

        $response->assertRedirect();

        $allocation->refresh();
        $this->assertSame('ready_for_release', $allocation->release_status);
        $this->assertDatabaseCount('sms_logs', 0);
    }

    public function test_sms_settings_route_disables_direct_allocation_ready_for_release_sms(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $fixtures = $this->makeFixtures($admin);
        $allocation = $this->makeDirectAllocationFixture($admin, $fixtures);

        $this->actingAs($admin)->post(route('sms.settings.automation'), [
            'send_on_event_ongoing' => '1',
            // Intentionally omitted: send_on_direct_assistance_status_change
        ])->assertRedirect(route('sms.index'));

        $response = $this->actingAs($admin)->post(route('allocations.mark-ready-for-release', $allocation));

        $response->assertRedirect();

        $allocation->refresh();
        $this->assertSame('ready_for_release', $allocation->release_status);
        $this->assertDatabaseCount('sms_logs', 0);
    }

    public function test_sms_settings_route_enables_direct_allocation_ready_for_release_sms(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $fixtures = $this->makeFixtures($admin);
        $allocation = $this->makeDirectAllocationFixture($admin, $fixtures);

        $this->actingAs($admin)->post(route('sms.settings.automation'), [
            'send_on_event_ongoing' => '0',
            'send_on_direct_assistance_status_change' => '1',
        ])->assertRedirect(route('sms.index'));

        $response = $this->actingAs($admin)->post(route('allocations.mark-ready-for-release', $allocation));

        $response->assertRedirect();

        $allocation->refresh();
        $this->assertSame('ready_for_release', $allocation->release_status);

        $this->assertDatabaseHas('sms_logs', [
            'beneficiary_id' => $fixtures['beneficiary']->id,
            'status' => 'sent',
        ]);
    }

    /**
     * @return array{agency: Agency, barangay: Barangay, program: ProgramName, resourceType: ResourceType, beneficiary: Beneficiary}
     */
    private function makeFixtures(User $actor): array
    {
        $agency = Agency::create([
            'name' => 'DA',
            'full_name' => 'Department of Agriculture',
            'is_active' => true,
        ]);

        $barangay = Barangay::create([
            'name' => 'Automation Barangay '.uniqid(),
            'latitude' => 10.40000000,
            'longitude' => 123.40000000,
        ]);

        $program = ProgramName::create([
            'agency_id' => $agency->id,
            'name' => 'Automation Program '.uniqid(),
            'description' => 'Automation test program',
            'is_active' => true,
            'classification' => 'Farmer',
        ]);

        $resourceType = ResourceType::create([
            'name' => 'Automation Resource '.uniqid(),
            'unit' => 'kg',
            'source_agency' => 'DA',
            'agency_id' => $agency->id,
        ]);

        $beneficiary = Beneficiary::create([
            'agency_id' => $agency->id,
            'first_name' => 'Automation',
            'last_name' => 'Receiver',
            'barangay_id' => $barangay->id,
            'classification' => 'Farmer',
            'contact_number' => '09170000123',
            'status' => 'Active',
            'registered_at' => now()->toDateString(),
        ]);

        return [
            'agency' => $agency,
            'barangay' => $barangay,
            'program' => $program,
            'resourceType' => $resourceType,
            'beneficiary' => $beneficiary,
        ];
    }

    /**
     * @param  array{agency: Agency, barangay: Barangay, program: ProgramName, resourceType: ResourceType, beneficiary: Beneficiary}  $fixtures
     */
    private function makeDirectAllocationFixture(User $admin, array $fixtures): Allocation
    {
        $event = DistributionEvent::create([
            'barangay_id' => $fixtures['barangay']->id,
            'resource_type_id' => $fixtures['resourceType']->id,
            'program_name_id' => $fixtures['program']->id,
            'distribution_date' => now()->toDateString(),
            'status' => 'Ongoing',
            'created_by' => $admin->id,
            'type' => 'physical',
            'total_fund_amount' => null,
        ]);

        return Allocation::create([
            'distribution_event_id' => $event->id,
            'release_method' => 'direct',
            'beneficiary_id' => $fixtures['beneficiary']->id,
            'program_name_id' => $fixtures['program']->id,
            'resource_type_id' => $fixtures['resourceType']->id,
            'quantity' => 2,
            'amount' => null,
            'remarks' => 'Direct allocation SMS automation test',
        ]);
    }
}
