<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Barangay;
use App\Models\Beneficiary;
use App\Models\FormFieldOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BeneficiaryNativeOptionNormalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_maps_native_option_codes_to_enum_labels_before_save(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $agency = Agency::create([
            'name' => 'DA',
            'full_name' => 'Department of Agriculture',
            'is_active' => true,
        ]);

        $barangay = Barangay::create([
            'name' => 'Normalization Barangay',
            'latitude' => 10.12345678,
            'longitude' => 123.12345678,
        ]);

        FormFieldOption::create([
            'field_group' => 'farm_ownership',
            'placement_section' => FormFieldOption::PLACEMENT_FARMER_INFORMATION,
            'label' => 'Owner',
            'value' => 'land_owner',
            'sort_order' => 10,
            'is_required' => true,
            'is_active' => true,
        ]);

        FormFieldOption::create([
            'field_group' => 'farm_type',
            'placement_section' => FormFieldOption::PLACEMENT_FARMER_INFORMATION,
            'label' => 'Rainfed Lowland',
            'value' => 'lowland_rainfed',
            'sort_order' => 10,
            'is_required' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('beneficiaries.store'), [
            'agency_id' => $agency->id,
            'first_name' => 'Maria',
            'middle_name' => 'Santos',
            'last_name' => 'Rivera',
            'name_suffix' => 'Jr.',
            'sex' => 'Female',
            'date_of_birth' => now()->subYears(30)->toDateString(),
            'home_address' => 'Sitio Proper',
            'barangay_id' => $barangay->id,
            'contact_number' => '09912345678',
            'civil_status' => 'Married',
            'highest_education' => 'Post Graduate',
            'id_type' => 'Postal ID',
            'status' => 'Active',
            'registered_at' => now()->toDateString(),
            'classification' => 'Farmer',
            'association_member' => 0,
            'association_name' => null,
            'rsbsa_number' => 'RSBSA-TEST-001',
            'farm_ownership' => 'land_owner',
            'farm_size_hectares' => '2.50',
            'primary_commodity' => 'Rice',
            'farm_type' => 'lowland_rainfed',
            'organization_membership' => 'Farmers Association',
        ]);

        $response
            ->assertRedirect(route('beneficiaries.index'))
            ->assertSessionHasNoErrors();

        $beneficiary = Beneficiary::query()->latest('id')->firstOrFail();

        $this->assertSame('Owner', $beneficiary->farm_ownership);
        $this->assertSame('Rainfed Lowland', $beneficiary->farm_type);
    }
}
