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
        [$admin, $agency, $barangay] = $this->createContext();
        $this->seedNativeFarmerOptions();

        $response = $this->actingAs($admin)->post(route('beneficiaries.store'), $this->beneficiaryPayload($agency, $barangay, [
            'contact_number' => '09912345678',
            'rsbsa_number' => 'RSBSA-TEST-001',
            'farm_ownership' => 'land_owner',
            'farm_type' => 'lowland_rainfed',
        ]));

        $response
            ->assertRedirect(route('beneficiaries.index'))
            ->assertSessionHasNoErrors();

        $beneficiary = Beneficiary::query()->latest('id')->firstOrFail();

        $this->assertSame('Owner', $beneficiary->farm_ownership);
        $this->assertSame('Rainfed Lowland', $beneficiary->farm_type);
    }

    public function test_store_accepts_flexible_ph_mobile_formats_and_normalizes_before_save(): void
    {
        [$admin, $agency] = $this->createContext();
        $this->seedNativeFarmerOptions();

        $cases = [
            ['raw' => '09170000001', 'normalized' => '09170000001'],
            ['raw' => '9170000002', 'normalized' => '09170000002'],
            ['raw' => '639170000003', 'normalized' => '09170000003'],
            ['raw' => '+639170000004', 'normalized' => '09170000004'],
            ['raw' => '+63 917-000-0005', 'normalized' => '09170000005'],
        ];

        foreach ($cases as $index => $case) {
            $caseBarangay = Barangay::create([
                'name' => 'Normalization Barangay '.($index + 1),
                'latitude' => 10.12345678 + (($index + 1) / 10000000),
                'longitude' => 123.12345678 + (($index + 1) / 10000000),
            ]);

            $response = $this->actingAs($admin)->post(route('beneficiaries.store'), $this->beneficiaryPayload($agency, $caseBarangay, [
                'first_name' => 'Maria'.$index,
                'last_name' => 'Rivera'.$index,
                'contact_number' => $case['raw'],
                'rsbsa_number' => 'RSBSA-FLEX-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'farm_ownership' => 'land_owner',
                'farm_type' => 'lowland_rainfed',
            ]));

            $response
                ->assertRedirect(route('beneficiaries.index'))
                ->assertSessionHasNoErrors();

            $this->assertDatabaseHas('beneficiaries', [
                'contact_number' => $case['normalized'],
                'rsbsa_number' => 'RSBSA-FLEX-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
            ]);
        }
    }

    public function test_store_rejects_non_mobile_contact_number(): void
    {
        [$admin, $agency, $barangay] = $this->createContext();
        $this->seedNativeFarmerOptions();

        $response = $this->actingAs($admin)->post(route('beneficiaries.store'), $this->beneficiaryPayload($agency, $barangay, [
            'contact_number' => '0281234567',
            'rsbsa_number' => 'RSBSA-INVALID-001',
            'farm_ownership' => 'land_owner',
            'farm_type' => 'lowland_rainfed',
        ]));

        $response->assertSessionHasErrors(['contact_number']);
        $this->assertDatabaseCount('beneficiaries', 0);
    }

    /**
     * @return array{0: User, 1: Agency, 2: Barangay}
     */
    private function createContext(): array
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

        return [$admin, $agency, $barangay];
    }

    private function seedNativeFarmerOptions(): void
    {
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
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function beneficiaryPayload(Agency $agency, Barangay $barangay, array $overrides = []): array
    {
        return array_merge([
            'agencies' => [$agency->id],
            'first_name' => 'Maria',
            'middle_name' => 'Santos',
            'last_name' => 'Rivera',
            'name_suffix' => 'Jr.',
            'sex' => 'Female',
            'date_of_birth' => now()->subYears(30)->toDateString(),
            'home_address' => 'Sitio Proper',
            'barangay_id' => $barangay->id,
            'contact_number' => '09170000000',
            'civil_status' => 'Married',
            'highest_education' => 'Post Graduate',
            'id_type' => 'Postal ID',
            'status' => 'Active',
            'registered_at' => now()->toDateString(),
            'classification' => 'Farmer',
            'association_member' => 0,
            'association_name' => null,
            'rsbsa_number' => 'RSBSA-TEST-DEFAULT',
            'farm_ownership' => 'Owner',
            'farm_size_hectares' => '2.50',
            'primary_commodity' => 'Rice',
            'farm_type' => 'Rainfed Lowland',
            'organization_membership' => 'Farmers Association',
        ], $overrides);
    }
}
