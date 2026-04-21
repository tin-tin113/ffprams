<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Allocation;
use App\Models\Barangay;
use App\Models\Beneficiary;
use App\Models\DistributionEvent;
use App\Models\ProgramName;
use App\Models\ResourceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistributionEventComplianceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_physical_event_can_be_created_without_financial_compliance_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$barangay, $resourceType, $program] = $this->makeEventFixtures();

        $response = $this->actingAs($admin)->post(route('distribution-events.store'), [
            'barangay_id' => $barangay->id,
            'resource_type_id' => $resourceType->id,
            'program_name_id' => $program->id,
            'distribution_date' => now()->toDateString(),
            'type' => 'physical',
        ]);

        $event = DistributionEvent::query()->latest('id')->firstOrFail();

        $response->assertRedirect(route('distribution-events.show', $event));
        $this->assertSame('physical', $event->type);
        $this->assertNull($event->total_fund_amount);
    }

    public function test_financial_event_can_be_created_with_incomplete_compliance_details(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$barangay, $resourceType, $program] = $this->makeEventFixtures();

        $response = $this->actingAs($admin)->post(route('distribution-events.store'), [
            'barangay_id' => $barangay->id,
            'resource_type_id' => $resourceType->id,
            'program_name_id' => $program->id,
            'distribution_date' => now()->toDateString(),
            'type' => 'financial',
            'total_fund_amount' => '50000.00',
        ]);

        $event = DistributionEvent::query()->latest('id')->firstOrFail();

        $response->assertRedirect(route('distribution-events.show', $event));
        $this->assertSame('financial', $event->type);
        $this->assertNotNull($event->compliance_field_states);
        $this->assertSame(
            DistributionEvent::COMPLIANCE_STATUS_NOT_AVAILABLE_YET,
            data_get($event->compliance_field_states, 'legal_basis_type.status')
        );
    }

    public function test_financial_event_can_move_to_ongoing_with_pending_compliance_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$barangay, $resourceType, $program] = $this->makeEventFixtures();

        $event = DistributionEvent::create([
            'barangay_id' => $barangay->id,
            'resource_type_id' => $resourceType->id,
            'program_name_id' => $program->id,
            'distribution_date' => now()->toDateString(),
            'status' => 'Pending',
            'created_by' => $admin->id,
            'type' => 'financial',
            'total_fund_amount' => 120000,
            'beneficiary_list_approved_at' => now(),
            'beneficiary_list_approved_by' => $admin->id,
            'compliance_field_states' => [
                'legal_basis_type' => [
                    'status' => DistributionEvent::COMPLIANCE_STATUS_NOT_AVAILABLE_YET,
                    'reason' => 'Awaiting documentation from municipal office.',
                ],
            ],
        ]);

        $response = $this->actingAs($admin)->post(route('distribution-events.updateStatus', $event), [
            'status' => 'Ongoing',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $event->refresh();
        $this->assertSame('Ongoing', $event->status);
    }

    public function test_financial_event_completion_is_blocked_when_critical_compliance_is_unresolved(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$barangay, $resourceType, $program] = $this->makeEventFixtures();

        $event = DistributionEvent::create([
            'barangay_id' => $barangay->id,
            'resource_type_id' => $resourceType->id,
            'program_name_id' => $program->id,
            'distribution_date' => now()->toDateString(),
            'status' => 'Ongoing',
            'created_by' => $admin->id,
            'type' => 'financial',
            'total_fund_amount' => 100000,
            'liquidation_status' => 'pending',
            'compliance_field_states' => [
                'legal_basis_reference_no' => [
                    'status' => DistributionEvent::COMPLIANCE_STATUS_NOT_AVAILABLE_YET,
                    'reason' => 'Resolution number not yet released.',
                ],
            ],
        ]);

        $response = $this->actingAs($admin)->post(route('distribution-events.updateStatus', $event), [
            'status' => 'Completed',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $event->refresh();
        $this->assertSame('Ongoing', $event->status);
    }

    public function test_financial_event_can_be_completed_when_critical_compliance_fields_are_satisfied(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$barangay, $resourceType, $program] = $this->makeEventFixtures();

        $event = DistributionEvent::create([
            'barangay_id' => $barangay->id,
            'resource_type_id' => $resourceType->id,
            'program_name_id' => $program->id,
            'distribution_date' => now()->toDateString(),
            'status' => 'Ongoing',
            'created_by' => $admin->id,
            'type' => 'financial',
            'total_fund_amount' => 90000,
            'legal_basis_type' => 'resolution',
            'legal_basis_reference_no' => 'RES-2026-099',
            'legal_basis_date' => now()->toDateString(),
            'fund_source' => 'other',
            'liquidation_status' => 'verified',
            'liquidation_due_date' => now()->subDays(3)->toDateString(),
            'liquidation_submitted_at' => now()->subDay(),
            'liquidation_reference_no' => 'LIQ-2026-321',
            'requires_farmc_endorsement' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('distribution-events.updateStatus', $event), [
            'status' => 'Completed',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $event->refresh();
        $this->assertSame('Completed', $event->status);
    }

    public function test_event_completion_is_blocked_until_all_beneficiaries_are_marked(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$barangay, $resourceType, $program] = $this->makeEventFixtures();

        $event = DistributionEvent::create([
            'barangay_id' => $barangay->id,
            'resource_type_id' => $resourceType->id,
            'program_name_id' => $program->id,
            'distribution_date' => now()->toDateString(),
            'status' => 'Ongoing',
            'created_by' => $admin->id,
            'type' => 'physical',
            'total_fund_amount' => null,
        ]);

        $this->createEventAllocation($event, $program, $resourceType, null);

        $response = $this->actingAs($admin)->post(route('distribution-events.updateStatus', $event), [
            'status' => 'Completed',
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('error');

        $event->refresh();
        $this->assertSame('Ongoing', $event->status);
    }

    public function test_event_completion_is_allowed_when_all_beneficiaries_are_marked(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$barangay, $resourceType, $program] = $this->makeEventFixtures();

        $event = DistributionEvent::create([
            'barangay_id' => $barangay->id,
            'resource_type_id' => $resourceType->id,
            'program_name_id' => $program->id,
            'distribution_date' => now()->toDateString(),
            'status' => 'Ongoing',
            'created_by' => $admin->id,
            'type' => 'physical',
            'total_fund_amount' => null,
        ]);

        $this->createEventAllocation($event, $program, $resourceType, 'received');
        $this->createEventAllocation($event, $program, $resourceType, 'not_received');

        $response = $this->actingAs($admin)->post(route('distribution-events.updateStatus', $event), [
            'status' => 'Completed',
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('success');

        $event->refresh();
        $this->assertSame('Completed', $event->status);
    }

    public function test_financial_event_completion_is_blocked_when_a_beneficiary_is_unmarked_even_if_compliance_is_ready(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$barangay, $resourceType, $program] = $this->makeEventFixtures();

        $event = DistributionEvent::create([
            'barangay_id' => $barangay->id,
            'resource_type_id' => $resourceType->id,
            'program_name_id' => $program->id,
            'distribution_date' => now()->toDateString(),
            'status' => 'Ongoing',
            'created_by' => $admin->id,
            'type' => 'financial',
            'total_fund_amount' => 90000,
            'legal_basis_type' => 'resolution',
            'legal_basis_reference_no' => 'RES-2026-200',
            'legal_basis_date' => now()->toDateString(),
            'fund_source' => 'other',
            'liquidation_status' => 'verified',
            'liquidation_due_date' => now()->subDays(2)->toDateString(),
            'liquidation_submitted_at' => now()->subDay(),
            'liquidation_reference_no' => 'LIQ-2026-200',
            'requires_farmc_endorsement' => false,
        ]);

        $this->createEventAllocation($event, $program, $resourceType, null);

        $response = $this->actingAs($admin)->post(route('distribution-events.updateStatus', $event), [
            'status' => 'Completed',
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('error');

        $event->refresh();
        $this->assertSame('Ongoing', $event->status);
    }

    public function test_update_compliance_requires_reason_for_non_provided_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = $this->makeFinancialEvent($admin, 'Pending');

        $response = $this->actingAs($admin)->post(route('distribution-events.updateCompliance', $event), [
            'compliance_states' => [
                'fund_source' => DistributionEvent::COMPLIANCE_STATUS_NOT_AVAILABLE_YET,
            ],
            'compliance_reasons' => [
                'fund_source' => '',
            ],
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHasErrors(['compliance_reasons.fund_source']);
    }

    public function test_update_compliance_stores_state_and_reason_for_non_provided_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = $this->makeFinancialEvent($admin, 'Pending');

        $response = $this->actingAs($admin)->post(route('distribution-events.updateCompliance', $event), [
            'compliance_states' => [
                'fund_source' => DistributionEvent::COMPLIANCE_STATUS_NOT_AVAILABLE_YET,
            ],
            'compliance_reasons' => [
                'fund_source' => 'Waiting for budget memo from accounting.',
            ],
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('success');

        $event->refresh();

        $this->assertSame(
            DistributionEvent::COMPLIANCE_STATUS_NOT_AVAILABLE_YET,
            data_get($event->compliance_field_states, 'fund_source.status')
        );
        $this->assertSame(
            'Waiting for budget memo from accounting.',
            data_get($event->compliance_field_states, 'fund_source.reason')
        );
    }

    /**
     * @return array{Barangay, ResourceType, ProgramName}
     */
    private function makeEventFixtures(): array
    {
        $agency = Agency::create([
            'name' => 'DA',
            'full_name' => 'Department of Agriculture',
            'is_active' => true,
        ]);

        $barangay = Barangay::create([
            'name' => 'Compliance Barangay '.uniqid(),
            'latitude' => 10.40000000,
            'longitude' => 123.40000000,
        ]);

        $program = ProgramName::create([
            'agency_id' => $agency->id,
            'name' => 'Compliance Program '.uniqid(),
            'description' => 'Compliance workflow test program',
            'is_active' => true,
            'classification' => 'Farmer',
        ]);

        $resourceType = ResourceType::create([
            'name' => 'Compliance Resource '.uniqid(),
            'unit' => 'PHP',
            'source_agency' => $agency->name,
            'agency_id' => $agency->id,
        ]);

        return [$barangay, $resourceType, $program];
    }

    private function makeFinancialEvent(User $admin, string $status): DistributionEvent
    {
        [$barangay, $resourceType, $program] = $this->makeEventFixtures();

        return DistributionEvent::create([
            'barangay_id' => $barangay->id,
            'resource_type_id' => $resourceType->id,
            'program_name_id' => $program->id,
            'distribution_date' => now()->toDateString(),
            'status' => $status,
            'created_by' => $admin->id,
            'type' => 'financial',
            'total_fund_amount' => 50000,
        ]);
    }

    private function createEventAllocation(
        DistributionEvent $event,
        ProgramName $program,
        ResourceType $resourceType,
        ?string $releaseOutcome,
    ): Allocation {
        $beneficiary = Beneficiary::create([
            'agency_id' => $program->agency_id,
            'first_name' => 'Release',
            'last_name' => 'Case '.uniqid(),
            'barangay_id' => $event->barangay_id,
            'classification' => 'Farmer',
            'contact_number' => '',
            'status' => 'Active',
            'registered_at' => now()->toDateString(),
        ]);

        return Allocation::create([
            'distribution_event_id' => $event->id,
            'release_method' => 'event',
            'beneficiary_id' => $beneficiary->id,
            'program_name_id' => $program->id,
            'resource_type_id' => $resourceType->id,
            'quantity' => $event->isFinancial() ? null : 1,
            'amount' => $event->isFinancial() ? 1000 : null,
            'release_outcome' => $releaseOutcome,
            'distributed_at' => $releaseOutcome === 'received' ? now() : null,
            'remarks' => 'Completion-gate test allocation',
        ]);
    }
}
