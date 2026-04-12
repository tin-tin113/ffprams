<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Barangay;
use App\Models\Beneficiary;
use Illuminate\Database\Seeder;

class BeneficiaryPerBarangaySeeder extends Seeder
{
    public function run(): void
    {
        $barangays = Barangay::query()->orderBy('id')->get();

        if ($barangays->isEmpty()) {
            $this->command?->warn('No barangays found. Run BarangaySeeder first.');

            return;
        }

        $agencies = Agency::query()->whereIn('name', ['DA', 'BFAR', 'DAR'])->get()->keyBy('name');
        $associationNames = [
            'Farmers Association',
            'Fisherfolk Cooperative',
            'Barangay Livelihood Group',
            'Rural Producers Association',
        ];
        $idTypes = ['PhilSys ID', 'Postal ID', "Voter's ID", "Driver's License", 'Passport'];
        $educationLevels = ['elementary', 'high_school', 'vocational', 'college', 'post_graduate'];

        $created = 0;

        foreach ($barangays as $barangay) {
            for ($i = 1; $i <= 10; $i++) {
                $profileType = ($i - 1) % 3;
                $agencyName = match ($profileType) {
                    0 => 'DA',
                    1 => 'BFAR',
                    default => 'DAR',
                };

                $agency = $agencies->get($agencyName);
                $classification = match ($agencyName) {
                    'DA' => 'Farmer',
                    'BFAR' => 'Fisherfolk',
                    default => 'Both',
                };

                $sex = fake()->randomElement(['Male', 'Female']);
                $associationMember = fake()->boolean(35);

                Beneficiary::create([
                    'agency_id' => $agency?->id,
                    'first_name' => fake()->firstName($sex === 'Male' ? 'male' : 'female'),
                    'middle_name' => fake()->optional(0.55)->lastName(),
                    'last_name' => fake()->lastName(),
                    'name_suffix' => fake()->optional(0.08)->randomElement(['Jr.', 'Sr.', 'II', 'III']),
                    'sex' => $sex,
                    'date_of_birth' => fake()->dateTimeBetween('-68 years', '-18 years')->format('Y-m-d'),
                    'home_address' => 'Sitio '.fake()->streetName().', Brgy. '.$barangay->name.', E.B. Magalona',
                    'barangay_id' => $barangay->id,
                    'classification' => $classification,
                    'contact_number' => $this->buildContactNumber((int) $barangay->id, $i),
                    'status' => 'Active',
                    'registered_at' => now()->subDays(fake()->numberBetween(0, 365))->toDateString(),
                    'civil_status' => fake()->randomElement(['Single', 'Married', 'Widowed', 'Separated']),
                    'id_type' => fake()->randomElement($idTypes),
                    'highest_education' => fake()->randomElement($educationLevels),
                    'association_member' => $associationMember,
                    'association_name' => $associationMember ? fake()->randomElement($associationNames) : null,
                    'rsbsa_number' => in_array($classification, ['Farmer', 'Both'], true)
                        ? $this->buildRegistryCode('RSBSA', (int) $barangay->id, $i)
                        : null,
                    'farm_ownership' => in_array($classification, ['Farmer', 'Both'], true)
                        ? fake()->randomElement(['Owner', 'Lessee', 'Share Tenant'])
                        : null,
                    'farm_size_hectares' => in_array($classification, ['Farmer', 'Both'], true)
                        ? fake()->randomFloat(2, 0.25, 6.50)
                        : null,
                    'primary_commodity' => in_array($classification, ['Farmer', 'Both'], true)
                        ? fake()->randomElement(['Rice', 'Corn', 'Vegetables', 'Sugarcane'])
                        : null,
                    'farm_type' => in_array($classification, ['Farmer', 'Both'], true)
                        ? fake()->randomElement(['Irrigated', 'Rainfed Lowland', 'Upland'])
                        : null,
                    'organization_membership' => in_array($classification, ['Farmer', 'Both'], true)
                        ? fake()->randomElement($associationNames)
                        : null,
                    'fishr_number' => in_array($classification, ['Fisherfolk', 'Both'], true)
                        ? $this->buildRegistryCode('FISHR', (int) $barangay->id, $i)
                        : null,
                    'fisherfolk_type' => in_array($classification, ['Fisherfolk', 'Both'], true)
                        ? fake()->randomElement(['Capture Fishing', 'Fish Farming', 'Fish Vendor', 'Fish Worker'])
                        : null,
                    'main_fishing_gear' => in_array($classification, ['Fisherfolk', 'Both'], true)
                        ? fake()->randomElement(['Gill Net', 'Hook and Line', 'Fish Trap', 'Cast Net'])
                        : null,
                    'has_fishing_vessel' => in_array($classification, ['Fisherfolk', 'Both'], true)
                        ? fake()->boolean(40)
                        : false,
                    'fishing_vessel_type' => in_array($classification, ['Fisherfolk', 'Both'], true)
                        ? fake()->randomElement(['Motorized', 'Non-Motorized'])
                        : null,
                    'fishing_vessel_tonnage' => in_array($classification, ['Fisherfolk', 'Both'], true)
                        ? fake()->randomFloat(2, 0.50, 4.50)
                        : null,
                    'length_of_residency_months' => in_array($classification, ['Fisherfolk', 'Both'], true)
                        ? fake()->numberBetween(12, 360)
                        : null,
                    'cloa_ep_number' => $agencyName === 'DAR'
                        ? $this->buildRegistryCode('DAR', (int) $barangay->id, $i)
                        : null,
                    'arb_classification' => $agencyName === 'DAR'
                        ? fake()->randomElement(['ARBs', 'Potential ARBs'])
                        : null,
                    'landholding_description' => $agencyName === 'DAR'
                        ? fake()->randomElement(['Irrigated rice land', 'Upland mixed crop area', 'Coconut and vegetable area'])
                        : null,
                    'land_area_awarded_hectares' => $agencyName === 'DAR'
                        ? fake()->randomFloat(2, 0.20, 5.00)
                        : null,
                    'ownership_scheme' => $agencyName === 'DAR'
                        ? fake()->randomElement(['Individual', 'Collective'])
                        : null,
                    'barc_membership_status' => $agencyName === 'DAR'
                        ? fake()->randomElement(['Member', 'Non-member'])
                        : null,
                ]);

                $created++;
            }
        }

        $this->command?->info("Created {$created} beneficiaries ({$barangays->count()} barangays x 10).");
    }

    private function buildContactNumber(int $barangayId, int $index): string
    {
        return '09'.str_pad((string) (($barangayId * 100) + $index), 9, '0', STR_PAD_LEFT);
    }

    private function buildRegistryCode(string $prefix, int $barangayId, int $index): string
    {
        return sprintf('%s-%02d-%02d-%s', $prefix, $barangayId, $index, strtoupper(fake()->bothify('??##')));
    }
}
