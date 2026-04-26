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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DirectAllocationAndReleaseFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_direct_allocation_store_requires_amount_for_financial_resource(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        [$beneficiary, $program, $financialResource] = $this->makeDirectAllocationFixtures('PHP');

        $response = $this->actingAs($admin)->post(route('allocations.store'), [
            'release_method' => 'direct',
            'beneficiary_id' => $beneficiary->id,
            'program_name_id' => $program->id,
            'resource_type_id' => $financialResource->id,
            'remarks' => 'Missing amount should fail validation.',
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHasErrors(['amount']);
    }

    public function test_direct_allocation_store_requires_quantity_for_non_financial_resource(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        [$beneficiary, $program, $nonFinancialResource] = $this->makeDirectAllocationFixtures('kg');

        $response = $this->actingAs($admin)->post(route('allocations.store'), [
            'release_method' => 'direct',
            'beneficiary_id' => $beneficiary->id,
            'program_name_id' => $program->id,
            'resource_type_id' => $nonFinancialResource->id,
            'remarks' => 'Missing quantity should fail validation.',
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHasErrors(['quantity']);
    }

    public function test_direct_allocation_store_normalizes_financial_payload(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        [$beneficiary, $program, $financialResource] = $this->makeDirectAllocationFixtures('PHP');

        $response = $this->actingAs($admin)->post(route('allocations.store'), [
            'release_method' => 'direct',
            'beneficiary_id' => $beneficiary->id,
            'program_name_id' => $program->id,
            'resource_type_id' => $financialResource->id,
            'amount' => '1250.50',
            'quantity' => '7.25',
            'remarks' => 'Financial payload normalization check.',
        ]);

        $response->assertRedirect(route('allocations.index'));

        $record = Allocation::query()->latest('id')->firstOrFail();

        $this->assertSame('1250.50', (string) $record->amount);
        $this->assertNull($record->quantity);
        $this->assertSame('planned', $record->release_status);
    }

    public function test_direct_allocation_mark_ready_for_release_sets_intermediate_state(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $allocation = $this->makeDirectAllocationFixture();

        $response = $this->actingAs($admin)->post(route('allocations.mark-ready-for-release', $allocation));

        $response->assertRedirect();

        $allocation->refresh();

        $this->assertTrue((bool) $allocation->is_ready_for_release);
        $this->assertNull($allocation->release_outcome);
        $this->assertNull($allocation->distributed_at);
        $this->assertSame('ready_for_release', $allocation->release_status);
    }

    public function test_direct_allocation_mark_released_requires_ready_for_release(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $allocation = $this->makeDirectAllocationFixture();

        $response = $this->actingAs($admin)->post(route('allocations.markDistributed', $allocation));

        $response
            ->assertRedirect()
            ->assertSessionHas('error');

        $allocation->refresh();

        $this->assertFalse((bool) $allocation->is_ready_for_release);
        $this->assertNull($allocation->release_outcome);
        $this->assertNull($allocation->distributed_at);
        $this->assertSame('planned', $allocation->release_status);
    }

    public function test_direct_allocation_ready_for_release_can_be_marked_released(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $allocation = $this->makeDirectAllocationFixture();

        $this->actingAs($admin)->post(route('allocations.mark-ready-for-release', $allocation))
            ->assertRedirect();

        $response = $this->actingAs($admin)->post(route('allocations.markDistributed', $allocation));

        $response->assertRedirect();

        $allocation->refresh();

        $this->assertFalse((bool) $allocation->is_ready_for_release);
        $this->assertSame('received', $allocation->release_outcome);
        $this->assertNotNull($allocation->distributed_at);
        $this->assertSame('released', $allocation->release_status);
    }

    public function test_allocation_csv_import_creates_allocations_for_valid_rows(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        [$event, $beneficiaryOne, $beneficiaryTwo] = $this->makeCsvImportFixtures($admin, 'kg');

        $csvContent = implode("\n", [
            'beneficiary_id,quantity,remarks',
            $beneficiaryOne->id.',10.50,First row',
            $beneficiaryTwo->id.',4.25,Second row',
        ]);

        $response = $this->actingAs($admin)->post(route('allocations.importCsv'), [
            'distribution_event_id' => $event->id,
            'form_context' => 'import_csv',
            'csv_file' => UploadedFile::fake()->createWithContent('allocations.csv', $csvContent),
        ]);

        $response->assertRedirect(route('distribution-events.show', $event) . '#tab-beneficiaries');

        $this->assertSame(
            2,
            Allocation::where('distribution_event_id', $event->id)->count()
        );
    }

    public function test_event_allocation_detail_can_use_event_scoped_url(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        [$event, $beneficiary] = $this->makeCsvImportFixtures($admin, 'kg');

        $allocation = Allocation::create([
            'release_method' => 'event',
            'distribution_event_id' => $event->id,
            'beneficiary_id' => $beneficiary->id,
            'program_name_id' => $event->program_name_id,
            'resource_type_id' => $event->resource_type_id,
            'quantity' => 5,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('distribution-events.allocations.show', [$event, $allocation]));

        $response
            ->assertOk()
            ->assertSee('Event Allocation')
            ->assertSee('Back to Event Beneficiaries');
    }

    /**
     * @return array{Beneficiary, ProgramName, ResourceType}
     */
    private function makeDirectAllocationFixtures(string $unit): array
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
            'contact_number' => '09123456789',
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

    private function makeDirectAllocationFixture(): Allocation
    {
        [$beneficiary, $program, $resourceType] = $this->makeDirectAllocationFixtures('kg');

        return Allocation::create([
            'release_method' => 'direct',
            'beneficiary_id' => $beneficiary->id,
            'program_name_id' => $program->id,
            'resource_type_id' => $resourceType->id,
            'quantity' => 2,
            'remarks' => 'Direct allocation release workflow test.',
            'created_by' => User::factory()->create()->id,
        ]);
    }

    /**
     * @return array{DistributionEvent, Beneficiary, Beneficiary, Beneficiary}
     */
    private function makeCsvImportFixtures(User $admin, string $unit): array
    {
        $agency = Agency::create([
            'name' => 'DA',
            'full_name' => 'Department of Agriculture',
            'is_active' => true,
        ]);

        $eventBarangay = Barangay::create([
            'name' => 'CSV Event Barangay '.uniqid(),
            'latitude' => 10.30000000,
            'longitude' => 123.30000000,
        ]);

        $otherBarangay = Barangay::create([
            'name' => 'CSV Other Barangay '.uniqid(),
            'latitude' => 10.31000000,
            'longitude' => 123.31000000,
        ]);

        $program = ProgramName::create([
            'agency_id' => $agency->id,
            'name' => 'CSV Program '.uniqid(),
            'description' => 'CSV import program',
            'is_active' => true,
            'classification' => 'Farmer',
        ]);

        $resourceType = ResourceType::create([
            'name' => 'CSV Resource '.uniqid(),
            'unit' => $unit,
            'source_agency' => $agency->name,
            'agency_id' => $agency->id,
        ]);

        $beneficiaryOne = Beneficiary::create([
            'agency_id' => $agency->id,
            'first_name' => 'CSV',
            'last_name' => 'Beneficiary One',
            'barangay_id' => $eventBarangay->id,
            'classification' => 'Farmer',
            'contact_number' => '09123456781',
            'status' => 'Active',
            'registered_at' => now()->toDateString(),
        ]);

        $beneficiaryTwo = Beneficiary::create([
            'agency_id' => $agency->id,
            'first_name' => 'CSV',
            'last_name' => 'Beneficiary Two',
            'barangay_id' => $eventBarangay->id,
            'classification' => 'Farmer',
            'contact_number' => '09123456782',
            'status' => 'Active',
            'registered_at' => now()->toDateString(),
        ]);

        $beneficiaryOtherBarangay = Beneficiary::create([
            'agency_id' => $agency->id,
            'first_name' => 'CSV',
            'last_name' => 'Other Barangay',
            'barangay_id' => $otherBarangay->id,
            'classification' => 'Farmer',
            'contact_number' => '09123456783',
            'status' => 'Active',
            'registered_at' => now()->toDateString(),
        ]);

        $event = DistributionEvent::create([
            'name' => 'CSV Import Event',
            'barangay_id' => $eventBarangay->id,
            'resource_type_id' => $resourceType->id,
            'program_name_id' => $program->id,
            'distribution_date' => now()->toDateString(),
            'status' => 'Pending',
            'created_by' => $admin->id,
            'type' => $unit === 'PHP' ? 'financial' : 'physical',
            'total_fund_amount' => $unit === 'PHP' ? 2000 : null,
        ]);

        return [$event, $beneficiaryOne, $beneficiaryTwo, $beneficiaryOtherBarangay];
    }
}
