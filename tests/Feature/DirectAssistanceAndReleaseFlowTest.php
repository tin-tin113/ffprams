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
use Tests\TestCase;

class DirectAssistanceAndReleaseFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_direct_assistance_store_requires_amount_for_financial_resource(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        [$beneficiary, $program, $financialResource] = $this->makeDirectAssistanceFixtures('PHP');

        $response = $this->actingAs($admin)->post(route('direct-assistance.store'), [
            'beneficiary_id' => $beneficiary->id,
            'program_name_id' => $program->id,
            'resource_type_id' => $financialResource->id,
            'remarks' => 'Missing amount should fail validation.',
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHasErrors(['amount']);
    }

    public function test_direct_assistance_store_requires_quantity_for_non_financial_resource(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        [$beneficiary, $program, $nonFinancialResource] = $this->makeDirectAssistanceFixtures('kg');

        $response = $this->actingAs($admin)->post(route('direct-assistance.store'), [
            'beneficiary_id' => $beneficiary->id,
            'program_name_id' => $program->id,
            'resource_type_id' => $nonFinancialResource->id,
            'remarks' => 'Missing quantity should fail validation.',
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHasErrors(['quantity']);
    }

    public function test_direct_assistance_store_normalizes_financial_payload(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        [$beneficiary, $program, $financialResource] = $this->makeDirectAssistanceFixtures('PHP');

        $response = $this->actingAs($admin)->post(route('direct-assistance.store'), [
            'beneficiary_id' => $beneficiary->id,
            'program_name_id' => $program->id,
            'resource_type_id' => $financialResource->id,
            'amount' => '1250.50',
            'quantity' => '7.25',
            'remarks' => 'Financial payload normalization check.',
        ]);

        $response->assertRedirect(route('direct-assistance.index'));

        $record = DirectAssistance::query()->latest('id')->firstOrFail();

        $this->assertSame('1250.50', (string) $record->amount);
        $this->assertNull($record->quantity);
        $this->assertSame('recorded', $record->status);
    }

    public function test_direct_assistance_update_requires_quantity_for_non_financial_resource(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        [$beneficiary, $program, $nonFinancialResource] = $this->makeDirectAssistanceFixtures('kg');

        $record = DirectAssistance::create([
            'beneficiary_id' => $beneficiary->id,
            'program_name_id' => $program->id,
            'resource_type_id' => $nonFinancialResource->id,
            'quantity' => 2,
            'amount' => null,
            'remarks' => 'Original record',
            'created_by' => $admin->id,
            'status' => 'recorded',
        ]);

        $response = $this->actingAs($admin)->put(route('direct-assistance.update', $record), [
            'program_name_id' => $program->id,
            'resource_type_id' => $nonFinancialResource->id,
            'amount' => null,
            'remarks' => 'Update without quantity should fail.',
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHasErrors(['quantity']);
    }

    public function test_allocation_mark_distributed_sets_received_outcome_and_timestamp(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        [$event, $allocation] = $this->makeAllocationFixtures($admin, 'Ongoing');

        $response = $this->actingAs($admin)->post(route('allocations.markDistributed', $allocation));

        $response->assertRedirect();

        $allocation->refresh();

        $this->assertSame('received', $allocation->release_outcome);
        $this->assertNotNull($allocation->distributed_at);
        $this->assertSame($event->id, $allocation->distribution_event_id);
    }

    public function test_allocation_mark_not_received_sets_not_received_outcome(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        [, $allocation] = $this->makeAllocationFixtures($admin, 'Ongoing');

        $response = $this->actingAs($admin)->post(route('allocations.markNotReceived', $allocation));

        $response->assertRedirect();

        $allocation->refresh();

        $this->assertSame('not_received', $allocation->release_outcome);
        $this->assertNull($allocation->distributed_at);
    }

    public function test_direct_assistance_mark_distributed_updates_status_and_distributor(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        [$beneficiary, $program, $resourceType] = $this->makeDirectAssistanceFixtures('PHP');

        $record = DirectAssistance::create([
            'beneficiary_id' => $beneficiary->id,
            'program_name_id' => $program->id,
            'resource_type_id' => $resourceType->id,
            'amount' => 900,
            'created_by' => $admin->id,
            'status' => 'recorded',
            'remarks' => 'Pending distribution',
        ]);

        $response = $this->actingAs($admin)->post(route('direct-assistance.mark-distributed', $record));

        $response->assertRedirect();

        $record->refresh();

        $this->assertSame('distributed', $record->status);
        $this->assertNotNull($record->distributed_at);
        $this->assertSame($admin->id, $record->distributed_by);
    }

    public function test_direct_assistance_mark_not_received_resets_distribution_state(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        [$beneficiary, $program, $resourceType] = $this->makeDirectAssistanceFixtures('PHP');

        $record = DirectAssistance::create([
            'beneficiary_id' => $beneficiary->id,
            'program_name_id' => $program->id,
            'resource_type_id' => $resourceType->id,
            'amount' => 700,
            'created_by' => $admin->id,
            'status' => 'recorded',
            'remarks' => 'Pending distribution',
        ]);

        $response = $this->actingAs($admin)->post(route('direct-assistance.mark-not-received', $record));

        $response->assertRedirect();

        $record->refresh();

        $this->assertSame('recorded', $record->status);
        $this->assertSame('not_received', $record->release_outcome);
        $this->assertNull($record->distributed_at);
        $this->assertNull($record->distributed_by);
    }

    /**
     * @return array{Beneficiary, ProgramName, ResourceType}
     */
    private function makeDirectAssistanceFixtures(string $unit): array
    {
        $agency = Agency::create([
            'name' => 'DA',
            'full_name' => 'Department of Agriculture',
            'is_active' => true,
        ]);

        $barangay = Barangay::create([
            'name' => 'Test Barangay ' . uniqid(),
            'latitude' => 10.10000000,
            'longitude' => 123.10000000,
        ]);

        $beneficiary = Beneficiary::create([
            'agency_id' => $agency->id,
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'barangay_id' => $barangay->id,
            'classification' => 'Farmer',
            'contact_number' => '',
            'status' => 'Active',
            'registered_at' => now()->toDateString(),
        ]);

        $program = ProgramName::create([
            'agency_id' => $agency->id,
            'name' => 'Program ' . uniqid(),
            'description' => 'Test program',
            'is_active' => true,
            'classification' => 'Farmer',
        ]);

        $resourceType = ResourceType::create([
            'name' => 'Resource ' . uniqid(),
            'unit' => $unit,
            'source_agency' => $agency->name,
            'agency_id' => $agency->id,
        ]);

        return [$beneficiary, $program, $resourceType];
    }

    /**
     * @return array{DistributionEvent, Allocation}
     */
    private function makeAllocationFixtures(User $admin, string $eventStatus): array
    {
        [$beneficiary, $program, $resourceType] = $this->makeDirectAssistanceFixtures('kg');

        $event = DistributionEvent::create([
            'barangay_id' => $beneficiary->barangay_id,
            'resource_type_id' => $resourceType->id,
            'program_name_id' => $program->id,
            'distribution_date' => now()->toDateString(),
            'status' => $eventStatus,
            'created_by' => $admin->id,
            'type' => 'physical',
            'total_fund_amount' => null,
        ]);

        $allocation = Allocation::create([
            'distribution_event_id' => $event->id,
            'release_method' => 'event',
            'beneficiary_id' => $beneficiary->id,
            'program_name_id' => $program->id,
            'resource_type_id' => $resourceType->id,
            'quantity' => 5,
            'amount' => null,
            'remarks' => 'Allocation for release outcome test.',
        ]);

        return [$event, $allocation];
    }
}
