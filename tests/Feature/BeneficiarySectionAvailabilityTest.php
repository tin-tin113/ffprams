<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\AgencyFormField;
use App\Models\Barangay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BeneficiarySectionAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_farmer_da_allows_general_rsbsa_unavailability_with_reason(): void
    {
        [$admin, $agency, $barangay] = $this->createContext();

        $response = $this->actingAs($admin)->post(route('beneficiaries.store'), $this->beneficiaryPayload($agency, $barangay, [
            'rsbsa_availability_status' => 'not_available_yet',
            'rsbsa_unavailability_reason' => 'Agency records are pending release from municipal agriculture office.',
            'rsbsa_number' => null,
            'farm_ownership' => null,
            'farm_size_hectares' => null,
            'primary_commodity' => null,
            'farm_type' => null,
        ]));

        $response
            ->assertRedirect(route('beneficiaries.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('beneficiaries', [
            'first_name' => 'General',
            'last_name' => 'Unavailable',
            'rsbsa_unavailability_reason' => 'Agency records are pending release from municipal agriculture office.',
        ]);
    }

    public function test_farmer_da_requires_reason_when_general_rsbsa_unavailable(): void
    {
        [$admin, $agency, $barangay] = $this->createContext();

        $response = $this->actingAs($admin)->post(route('beneficiaries.store'), $this->beneficiaryPayload($agency, $barangay, [
            'rsbsa_availability_status' => 'not_available_yet',
            'rsbsa_unavailability_reason' => '',
            'farm_ownership' => null,
            'farm_size_hectares' => null,
            'primary_commodity' => null,
            'farm_type' => null,
        ]));

        $response->assertSessionHasErrors(['rsbsa_unavailability_reason']);
    }

    public function test_sets_default_reason_for_not_applicable_status_when_agency_or_classification_context_is_missing(): void
    {
        [$admin, $agency, $barangay] = $this->createContext();

        $response = $this->actingAs($admin)->post(route('beneficiaries.store'), $this->beneficiaryPayload($agency, $barangay, [
            'classification' => 'Fisherfolk',
            'rsbsa_availability_status' => 'not_applicable',
            'rsbsa_unavailability_reason' => '',
            'fishr_availability_status' => 'provided',
            'fisherfolk_type' => 'Capture Fishing',
            'length_of_residency_months' => '24',
        ]));

        $response
            ->assertRedirect(route('beneficiaries.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('beneficiaries', [
            'first_name' => 'General',
            'last_name' => 'Unavailable',
            'classification' => 'Fisherfolk',
            'rsbsa_unavailability_reason' => 'Specific agency or classification is missing for this section.',
        ]);
    }

    /**
     * @return array{0: User, 1: Agency, 2: Barangay}
     */
    private function createContext(): array
    {
        $this->seed(\Database\Seeders\FormFieldOptionSeeder::class);
        $admin = User::factory()->create(['role' => 'admin']);

        $agency = Agency::create([
            'name' => 'DA',
            'full_name' => 'Department of Agriculture',
            'is_active' => true,
        ]);

        foreach (['Farmer', 'Fisherfolk'] as $classificationName) {
            $classification = \App\Models\Classification::create([
                'name' => $classificationName,
                'description' => $classificationName . ' classification',
            ]);
            $classification->agencies()->attach($agency->id);
        }

        AgencyFormField::create([
            'agency_id' => $agency->id,
            'field_name' => 'rsbsa_number',
            'display_label' => 'RSBSA Number',
            'field_type' => 'text',
            'is_required' => false,
            'is_active' => true,
            'form_section' => 'farmer_information',
            'sort_order' => 1,
        ]);

        $barangay = Barangay::create([
            'name' => 'Section Availability Barangay',
            'latitude' => 10.22345678,
            'longitude' => 123.22345678,
        ]);

        return [$admin, $agency, $barangay];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function beneficiaryPayload(Agency $agency, Barangay $barangay, array $overrides = []): array
    {
        return array_merge([
            'agencies' => [$agency->id],
            'first_name' => 'General',
            'middle_name' => 'Reason',
            'last_name' => 'Unavailable',
            'name_suffix' => '',
            'sex' => 'Female',
            'date_of_birth' => now()->subYears(30)->toDateString(),
            'home_address' => 'Sitio Proper',
            'barangay_id' => $barangay->id,
            'contact_number' => '09170000011',
            'civil_status' => 'Married',
            'highest_education' => 'College',
            'id_type' => 'Postal ID',
            'status' => 'Active',
            'registered_at' => now()->toDateString(),
            'classification' => 'Farmer',
            'association_member' => 0,
            'association_name' => null,
            'rsbsa_availability_status' => 'provided',
            'rsbsa_number' => 'RSBSA-SEC-001',
            'farm_ownership' => 'Owner',
            'farm_size_hectares' => '1.20',
            'primary_commodity' => 'Corn',
            'farm_type' => 'Irrigated',
            'organization_membership' => null,
        ], $overrides);
    }
}
