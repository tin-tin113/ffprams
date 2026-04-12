<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Barangay;
use App\Models\Beneficiary;
use App\Models\BeneficiaryAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BeneficiaryIndexExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_beneficiary_index_supports_advanced_filters_sort_and_page_size(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $agencyDa = Agency::create([
            'name' => 'DA',
            'full_name' => 'Department of Agriculture',
            'is_active' => true,
        ]);

        $agencyBfar = Agency::create([
            'name' => 'BFAR',
            'full_name' => 'Bureau of Fisheries and Aquatic Resources',
            'is_active' => true,
        ]);

        $barangayA = Barangay::create([
            'name' => 'Barangay Alpha',
            'latitude' => 10.11111111,
            'longitude' => 123.11111111,
        ]);

        $barangayB = Barangay::create([
            'name' => 'Barangay Beta',
            'latitude' => 10.22222222,
            'longitude' => 123.22222222,
        ]);

        for ($i = 1; $i <= 30; $i++) {
            $this->createBeneficiary(
                agency: $i % 2 === 0 ? $agencyDa : $agencyBfar,
                barangay: $i % 2 === 0 ? $barangayA : $barangayB,
                firstName: 'Person',
                lastName: sprintf('%02d', $i),
                classification: $i % 2 === 0 ? 'Farmer' : 'Fisherfolk',
                contactNumber: '0919'.str_pad((string) $i, 7, '0', STR_PAD_LEFT),
            );
        }

        $target = $this->createBeneficiary(
            agency: $agencyDa,
            barangay: $barangayA,
            firstName: 'Search',
            lastName: 'Target',
            classification: 'Farmer',
            contactNumber: '09197777000',
        );

        $targetWithoutDocs = $this->createBeneficiary(
            agency: $agencyDa,
            barangay: $barangayA,
            firstName: 'Search',
            lastName: 'NoDocs',
            classification: 'Farmer',
            contactNumber: '09197777111',
        );

        BeneficiaryAttachment::create([
            'beneficiary_id' => $target->id,
            'uploaded_by' => $admin->id,
            'document_type' => 'ID',
            'original_name' => 'target-id.pdf',
            'stored_name' => 'target-id.pdf',
            'path' => 'beneficiaries/'.$target->id.'/target-id.pdf',
            'disk' => 'beneficiary_documents',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size_bytes' => 1024,
            'sha256' => str_repeat('a', 64),
        ]);

        $response = $this->actingAs($admin)->get(route('beneficiaries.index', [
            'search' => '7777',
            'classification' => 'Farmer',
            'agency_id' => $agencyDa->id,
            'documents' => 'with',
            'per_page' => 25,
        ]));

        $response->assertOk();

        $paginator = $response->viewData('beneficiaries');

        $this->assertSame(25, $paginator->perPage());
        $this->assertSame(1, $paginator->total());
        $this->assertSame($target->id, $paginator->getCollection()->first()->id);

        $withoutDocsResponse = $this->actingAs($admin)->get(route('beneficiaries.index', [
            'search' => '7777',
            'classification' => 'Farmer',
            'agency_id' => $agencyDa->id,
            'documents' => 'without',
            'per_page' => 25,
        ]));

        $withoutDocsResponse->assertOk();

        $withoutDocsPaginator = $withoutDocsResponse->viewData('beneficiaries');

        $this->assertSame(1, $withoutDocsPaginator->total());
        $this->assertSame($targetWithoutDocs->id, $withoutDocsPaginator->getCollection()->first()->id);
    }

    public function test_bulk_status_update_changes_selected_beneficiaries(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $agency = Agency::create([
            'name' => 'DA',
            'full_name' => 'Department of Agriculture',
            'is_active' => true,
        ]);

        $barangay = Barangay::create([
            'name' => 'Barangay Delta',
            'latitude' => 10.30000000,
            'longitude' => 123.30000000,
        ]);

        $beneficiaryOne = $this->createBeneficiary(
            agency: $agency,
            barangay: $barangay,
            firstName: 'Status',
            lastName: 'One',
            classification: 'Farmer',
            contactNumber: '09181111111',
        );

        $beneficiaryTwo = $this->createBeneficiary(
            agency: $agency,
            barangay: $barangay,
            firstName: 'Status',
            lastName: 'Two',
            classification: 'Farmer',
            contactNumber: '09182222222',
        );

        $response = $this->actingAs($admin)->post(route('beneficiaries.bulkStatus'), [
            'selected_ids' => [$beneficiaryOne->id, $beneficiaryTwo->id],
            'status' => 'Inactive',
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('beneficiaries', [
            'id' => $beneficiaryOne->id,
            'status' => 'Inactive',
        ]);

        $this->assertDatabaseHas('beneficiaries', [
            'id' => $beneficiaryTwo->id,
            'status' => 'Inactive',
        ]);
    }

    private function createBeneficiary(
        Agency $agency,
        Barangay $barangay,
        string $firstName,
        string $lastName,
        string $classification,
        string $contactNumber,
    ): Beneficiary {
        return Beneficiary::create([
            'agency_id' => $agency->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'barangay_id' => $barangay->id,
            'classification' => $classification,
            'contact_number' => $contactNumber,
            'status' => 'Active',
            'registered_at' => now()->toDateString(),
        ]);
    }
}
