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
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmsBroadcastTargetingTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_rejects_send_all_recipient_type(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($user)->postJson(route('sms.preview'), [
            'recipient_type' => 'all',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['recipient_type']);
    }

    public function test_send_rejects_send_all_recipient_type(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($user)->postJson(route('sms.send'), [
            'recipient_type' => 'all',
            'message' => 'This should be blocked by validation.',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['recipient_type']);
    }

    public function test_preview_by_program_includes_only_active_program_recipients(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $fixtures = $this->makeSmsFixtures($user);

        $response = $this->actingAs($user)->postJson(route('sms.preview'), [
            'recipient_type' => 'by_program',
            'program_name_id' => $fixtures['programTarget']->id,
        ]);

        $response->assertOk()->assertJsonPath('count', 2);

        $recipientIds = collect($response->json('recipients'))->pluck('id');

        $this->assertTrue($recipientIds->contains($fixtures['beneficiaryAllocation']->id));
        $this->assertTrue($recipientIds->contains($fixtures['beneficiaryDirect']->id));
        $this->assertFalse($recipientIds->contains($fixtures['beneficiaryOther']->id));
        $this->assertFalse($recipientIds->contains($fixtures['beneficiaryInactive']->id));
    }

    public function test_preview_by_event_includes_only_active_event_recipients(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $fixtures = $this->makeSmsFixtures($user);

        $response = $this->actingAs($user)->postJson(route('sms.preview'), [
            'recipient_type' => 'by_event',
            'distribution_event_id' => $fixtures['eventTarget']->id,
        ]);

        $response->assertOk()->assertJsonPath('count', 2);

        $recipientIds = collect($response->json('recipients'))->pluck('id');

        $this->assertTrue($recipientIds->contains($fixtures['beneficiaryAllocation']->id));
        $this->assertTrue($recipientIds->contains($fixtures['beneficiaryDirect']->id));
        $this->assertFalse($recipientIds->contains($fixtures['beneficiaryOther']->id));
        $this->assertFalse($recipientIds->contains($fixtures['beneficiaryInactive']->id));
    }

    public function test_preview_by_event_rejects_completed_event(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $fixtures = $this->makeSmsFixtures($user);

        $response = $this->actingAs($user)->postJson(route('sms.preview'), [
            'recipient_type' => 'by_event',
            'distribution_event_id' => $fixtures['eventCompleted']->id,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['distribution_event_id']);
    }

    public function test_send_by_program_sends_only_targeted_recipients(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $fixtures = $this->makeSmsFixtures($user);

        config()->set('services.sms.api_key', 'test-api-key');
        config()->set('services.sms.api_url', 'https://example.test/sms');

        Http::fake([
            '*' => Http::response(['success' => true], 200),
        ]);

        $response = $this->actingAs($user)->postJson(route('sms.send'), [
            'recipient_type' => 'by_program',
            'program_name_id' => $fixtures['programTarget']->id,
            'message' => 'Program-targeted announcement.',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'sent' => 2,
                'failed' => 0,
                'total' => 2,
            ]);

        $this->assertDatabaseHas('sms_logs', [
            'beneficiary_id' => $fixtures['beneficiaryAllocation']->id,
            'status' => 'sent',
        ]);

        $this->assertDatabaseHas('sms_logs', [
            'beneficiary_id' => $fixtures['beneficiaryDirect']->id,
            'status' => 'sent',
        ]);

        $this->assertDatabaseMissing('sms_logs', [
            'beneficiary_id' => $fixtures['beneficiaryOther']->id,
            'message' => 'Program-targeted announcement.',
        ]);
    }

    public function test_preview_by_barangay_still_works(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $fixtures = $this->makeSmsFixtures($user);

        $response = $this->actingAs($user)->postJson(route('sms.preview'), [
            'recipient_type' => 'by_barangay',
            'barangay_id' => $fixtures['barangayTarget']->id,
        ]);

        $response->assertOk()->assertJsonPath('count', 2);
    }

    public function test_preview_selected_still_works(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $fixtures = $this->makeSmsFixtures($user);

        $response = $this->actingAs($user)->postJson(route('sms.preview'), [
            'recipient_type' => 'selected',
            'beneficiary_ids' => [
                $fixtures['beneficiaryAllocation']->id,
                $fixtures['beneficiaryDirect']->id,
                $fixtures['beneficiaryOther']->id,
            ],
        ]);

        $response->assertOk()->assertJsonPath('count', 3);
    }

    /**
     * @return array<string, mixed>
     */
    private function makeSmsFixtures(User $actor): array
    {
        $agency = Agency::create([
            'name' => 'DA',
            'full_name' => 'Department of Agriculture',
            'is_active' => true,
        ]);

        $barangayTarget = Barangay::create([
            'name' => 'SMS Target Barangay '.uniqid(),
            'latitude' => 10.50000000,
            'longitude' => 123.50000000,
        ]);

        $barangayOther = Barangay::create([
            'name' => 'SMS Other Barangay '.uniqid(),
            'latitude' => 10.51000000,
            'longitude' => 123.51000000,
        ]);

        $programTarget = ProgramName::create([
            'agency_id' => $agency->id,
            'name' => 'SMS Program Target '.uniqid(),
            'description' => 'Target program',
            'is_active' => true,
            'classification' => 'Farmer',
        ]);

        $programOther = ProgramName::create([
            'agency_id' => $agency->id,
            'name' => 'SMS Program Other '.uniqid(),
            'description' => 'Other program',
            'is_active' => true,
            'classification' => 'Farmer',
        ]);

        $resourceType = ResourceType::create([
            'name' => 'SMS Resource '.uniqid(),
            'unit' => 'kg',
            'source_agency' => 'DA',
            'agency_id' => $agency->id,
        ]);

        $eventTarget = DistributionEvent::create([
            'barangay_id' => $barangayTarget->id,
            'resource_type_id' => $resourceType->id,
            'program_name_id' => $programTarget->id,
            'distribution_date' => now()->toDateString(),
            'status' => 'Pending',
            'created_by' => $actor->id,
            'type' => 'physical',
            'total_fund_amount' => null,
        ]);

        $eventOther = DistributionEvent::create([
            'barangay_id' => $barangayOther->id,
            'resource_type_id' => $resourceType->id,
            'program_name_id' => $programOther->id,
            'distribution_date' => now()->toDateString(),
            'status' => 'Ongoing',
            'created_by' => $actor->id,
            'type' => 'physical',
            'total_fund_amount' => null,
        ]);

        $eventCompleted = DistributionEvent::create([
            'barangay_id' => $barangayOther->id,
            'resource_type_id' => $resourceType->id,
            'program_name_id' => $programTarget->id,
            'distribution_date' => now()->toDateString(),
            'status' => 'Completed',
            'created_by' => $actor->id,
            'type' => 'physical',
            'total_fund_amount' => null,
        ]);

        $beneficiaryAllocation = $this->createBeneficiary($agency, $barangayTarget, 'Program', 'Allocation', '09170000001');
        $beneficiaryDirect = $this->createBeneficiary($agency, $barangayTarget, 'Program', 'Direct', '09170000002');
        $beneficiaryOther = $this->createBeneficiary($agency, $barangayOther, 'Program', 'Other', '09170000003');
        $beneficiaryInactive = $this->createBeneficiary($agency, $barangayTarget, 'Program', 'Inactive', '09170000004', 'Inactive');

        Allocation::create([
            'distribution_event_id' => $eventTarget->id,
            'release_method' => 'event',
            'beneficiary_id' => $beneficiaryAllocation->id,
            'program_name_id' => $programTarget->id,
            'resource_type_id' => $resourceType->id,
            'quantity' => 5,
            'amount' => null,
            'remarks' => 'Target allocation',
        ]);

        Allocation::create([
            'distribution_event_id' => $eventTarget->id,
            'release_method' => 'event',
            'beneficiary_id' => $beneficiaryInactive->id,
            'program_name_id' => $programTarget->id,
            'resource_type_id' => $resourceType->id,
            'quantity' => 3,
            'amount' => null,
            'remarks' => 'Inactive allocation',
        ]);

        Allocation::create([
            'distribution_event_id' => $eventOther->id,
            'release_method' => 'event',
            'beneficiary_id' => $beneficiaryOther->id,
            'program_name_id' => $programOther->id,
            'resource_type_id' => $resourceType->id,
            'quantity' => 4,
            'amount' => null,
            'remarks' => 'Other allocation',
        ]);

        DirectAssistance::create([
            'beneficiary_id' => $beneficiaryDirect->id,
            'program_name_id' => $programTarget->id,
            'resource_type_id' => $resourceType->id,
            'quantity' => 2,
            'amount' => null,
            'remarks' => 'Target direct assistance',
            'created_by' => $actor->id,
            'status' => 'planned',
            'distribution_event_id' => $eventTarget->id,
        ]);

        return [
            'barangayTarget' => $barangayTarget,
            'programTarget' => $programTarget,
            'eventTarget' => $eventTarget,
            'eventCompleted' => $eventCompleted,
            'beneficiaryAllocation' => $beneficiaryAllocation,
            'beneficiaryDirect' => $beneficiaryDirect,
            'beneficiaryOther' => $beneficiaryOther,
            'beneficiaryInactive' => $beneficiaryInactive,
        ];
    }

    private function createBeneficiary(
        Agency $agency,
        Barangay $barangay,
        string $firstName,
        string $lastName,
        string $contactNumber,
        string $status = 'Active'
    ): Beneficiary {
        return Beneficiary::create([
            'agency_id' => $agency->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'barangay_id' => $barangay->id,
            'classification' => 'Farmer',
            'contact_number' => $contactNumber,
            'status' => $status,
            'registered_at' => now()->toDateString(),
        ]);
    }
}
