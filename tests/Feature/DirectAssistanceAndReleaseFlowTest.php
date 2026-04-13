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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        $this->assertSame('planned', $record->status);
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
            'status' => 'planned',
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
            'status' => 'planned',
            'remarks' => 'Pending distribution',
        ]);

        $readyResponse = $this->actingAs($admin)->post(route('direct-assistance.mark-ready-for-release', $record));

        $readyResponse->assertRedirect();

        $response = $this->actingAs($admin)->post(route('direct-assistance.mark-released', $record));

        $response->assertRedirect();

        $record->refresh();

        $this->assertSame('released', $record->status);
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
            'status' => 'planned',
            'remarks' => 'Pending distribution',
        ]);

        $readyResponse = $this->actingAs($admin)->post(route('direct-assistance.mark-ready-for-release', $record));

        $readyResponse->assertRedirect();

        $response = $this->actingAs($admin)->post(route('direct-assistance.mark-not-received', $record));

        $response->assertRedirect();

        $record->refresh();

        $this->assertSame('not_received', $record->status);
        $this->assertNull($record->release_outcome);
        $this->assertNull($record->distributed_at);
        $this->assertNull($record->distributed_by);
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

        $response->assertRedirect(route('distribution-events.show', $event));

        $this->assertSame(
            2,
            Allocation::where('distribution_event_id', $event->id)->count()
        );
    }

    public function test_allocation_csv_import_skips_invalid_rows_and_keeps_valid_rows(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        [$event, $beneficiaryOne, $beneficiaryTwo, $beneficiaryOtherBarangay] = $this->makeCsvImportFixtures($admin, 'kg');

        $csvContent = implode("\n", [
            'beneficiary_id,quantity,remarks',
            $beneficiaryOne->id.',8.00,Valid row',
            $beneficiaryOne->id.',2.00,Duplicate in same file',
            $beneficiaryOtherBarangay->id.',3.50,Mismatched barangay',
            $beneficiaryTwo->id.',0,Invalid quantity',
        ]);

        $response = $this->actingAs($admin)->post(route('allocations.importCsv'), [
            'distribution_event_id' => $event->id,
            'form_context' => 'import_csv',
            'csv_file' => UploadedFile::fake()->createWithContent('allocations.csv', $csvContent),
        ]);

        $response->assertRedirect(route('distribution-events.show', $event));

        $eventAllocations = Allocation::where('distribution_event_id', $event->id)->get();

        $this->assertCount(1, $eventAllocations);
        $this->assertSame($beneficiaryOne->id, $eventAllocations->first()->beneficiary_id);
    }

    public function test_allocation_csv_import_rejects_missing_required_header(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        [$event, $beneficiaryOne] = $this->makeCsvImportFixtures($admin, 'kg');

        $csvContent = implode("\n", [
            'beneficiary_id,remarks',
            $beneficiaryOne->id.',Missing quantity header',
        ]);

        $response = $this->actingAs($admin)->post(route('allocations.importCsv'), [
            'distribution_event_id' => $event->id,
            'form_context' => 'import_csv',
            'csv_file' => UploadedFile::fake()->createWithContent('allocations.csv', $csvContent),
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(0, Allocation::where('distribution_event_id', $event->id)->count());
    }

    public function test_allocation_csv_import_financial_respects_budget_limit(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        [$event, $beneficiaryOne, $beneficiaryTwo] = $this->makeCsvImportFixtures($admin, 'PHP');

        $csvContent = implode("\n", [
            'beneficiary_id,amount,remarks',
            $beneficiaryOne->id.',1500.00,First valid row',
            $beneficiaryTwo->id.',1000.00,Should exceed remaining budget',
        ]);

        $response = $this->actingAs($admin)->post(route('allocations.importCsv'), [
            'distribution_event_id' => $event->id,
            'form_context' => 'import_csv',
            'csv_file' => UploadedFile::fake()->createWithContent('allocations.csv', $csvContent),
        ]);

        $response->assertRedirect(route('distribution-events.show', $event));

        $allocations = Allocation::where('distribution_event_id', $event->id)->get();
        $this->assertCount(1, $allocations);
        $this->assertSame($beneficiaryOne->id, $allocations->first()->beneficiary_id);
        $this->assertSame('1500.00', (string) $allocations->first()->amount);
    }

    public function test_allocation_csv_template_download_returns_csv_header_for_event_type(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        [$event] = $this->makeCsvImportFixtures($admin, 'kg');

        $response = $this->actingAs($admin)->get(route('allocations.importCsvTemplate', [
            'distribution_event_id' => $event->id,
        ]));

        $response
            ->assertOk()
            ->assertHeader('content-disposition');

        $content = $response->streamedContent();

        $this->assertStringContainsString('beneficiary_id,quantity,assistance_purpose_id,remarks', $content);
    }

    public function test_allocation_csv_import_generates_downloadable_error_report_for_skipped_rows(): void
    {
        Storage::fake('allocation_import_reports');

        $admin = User::factory()->create(['role' => 'admin']);

        [$event, $beneficiaryOne, $beneficiaryTwo] = $this->makeCsvImportFixtures($admin, 'kg');

        $csvContent = implode("\n", [
            'beneficiary_id,quantity,remarks',
            $beneficiaryOne->id.',6.00,Valid row',
            $beneficiaryTwo->id.',0,Invalid quantity for report',
        ]);

        $response = $this->actingAs($admin)->post(route('allocations.importCsv'), [
            'distribution_event_id' => $event->id,
            'form_context' => 'import_csv',
            'csv_file' => UploadedFile::fake()->createWithContent('allocations.csv', $csvContent),
        ]);

        $response->assertRedirect(route('distribution-events.show', $event));
        $response->assertSessionHas('import_error_report_file');

        $reportFile = $response->getSession()->get('import_error_report_file');

        $this->assertIsString($reportFile);
        $this->assertNotSame('', $reportFile);

        $this->assertTrue(Storage::disk('allocation_import_reports')->exists($reportFile));

        $downloadResponse = $this->actingAs($admin)->get(route('allocations.importCsvErrorsReport', [
            'report' => $reportFile,
        ]));

        $downloadResponse
            ->assertOk()
            ->assertHeader('content-disposition');
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

    private function makeDirectAllocationFixture(): Allocation
    {
        [$beneficiary, $program, $resourceType] = $this->makeDirectAssistanceFixtures('kg');

        $event = DistributionEvent::create([
            'barangay_id' => $beneficiary->barangay_id,
            'resource_type_id' => $resourceType->id,
            'program_name_id' => $program->id,
            'distribution_date' => now()->toDateString(),
            'status' => 'Ongoing',
            'created_by' => User::factory()->create(['role' => 'admin'])->id,
            'type' => 'physical',
            'total_fund_amount' => null,
        ]);

        return Allocation::create([
            'distribution_event_id' => $event->id,
            'release_method' => 'direct',
            'beneficiary_id' => $beneficiary->id,
            'program_name_id' => $program->id,
            'resource_type_id' => $resourceType->id,
            'quantity' => 2,
            'amount' => null,
            'remarks' => 'Direct allocation release workflow test.',
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
            'contact_number' => '',
            'status' => 'Active',
            'registered_at' => now()->toDateString(),
        ]);

        $beneficiaryTwo = Beneficiary::create([
            'agency_id' => $agency->id,
            'first_name' => 'CSV',
            'last_name' => 'Beneficiary Two',
            'barangay_id' => $eventBarangay->id,
            'classification' => 'Farmer',
            'contact_number' => '',
            'status' => 'Active',
            'registered_at' => now()->toDateString(),
        ]);

        $beneficiaryOtherBarangay = Beneficiary::create([
            'agency_id' => $agency->id,
            'first_name' => 'CSV',
            'last_name' => 'Other Barangay',
            'barangay_id' => $otherBarangay->id,
            'classification' => 'Farmer',
            'contact_number' => '',
            'status' => 'Active',
            'registered_at' => now()->toDateString(),
        ]);

        $event = DistributionEvent::create([
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
