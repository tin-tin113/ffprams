<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Barangay;
use App\Models\Beneficiary;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BeneficiaryPerBarangaySeeder extends Seeder
{
    public function run(): void
    {
        // Skip if we already reached target (for idempotency)
        if (Beneficiary::count() >= 1000) {
            $this->command?->info('1,000+ beneficiaries already seeded. Skipping.');
            return;
        }

        $barangays = Barangay::query()->orderBy('id')->get();

        if ($barangays->isEmpty()) {
            $this->command?->warn('No barangays found. Run BarangaySeeder first.');
            return;
        }

        $agencies = Agency::query()->whereIn('name', ['DA', 'BFAR', 'DAR'])->get()->keyBy('name');
        $darAgencyId = $agencies->get('DAR')?->id;

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
            // Seed 44 per barangay to reach ~1,000 total (23 * 44 = 1,012)
            for ($i = 1; $i <= 44; $i++) {
                $profileType = ($i - 1) % 4; 
                $agencyName = match ($profileType) {
                    0 => 'DA',
                    1 => 'BFAR',
                    2 => 'DAR',
                    3 => 'DA',
                };

                $agency = $agencies->get($agencyName);
                
                $classification = match ($profileType) {
                    1 => 'Fisherfolk',
                    3 => 'Farmer & Fisherfolk',
                    default => 'Farmer',
                };

                $sex = fake()->randomElement(['Male', 'Female']);
                $associationMember = fake()->boolean(35);

                $firstName  = fake()->firstName($sex === 'Male' ? 'male' : 'female');
                $middleName = fake()->optional(0.55)->lastName();
                $lastName   = fake()->lastName();
                $nameSuffix = fake()->optional(0.08)->randomElement(['Jr.', 'Sr.', 'II', 'III']);
                $fullName   = trim(implode(' ', array_filter([$firstName, $middleName, $lastName, $nameSuffix])));

                $rsbsa = (str_contains($classification, 'Farmer')) 
                    ? $this->buildRegistryCode('RSBSA', (int) $barangay->id, $i) 
                    : null;
                $fishr = (str_contains($classification, 'Fisherfolk')) 
                    ? $this->buildRegistryCode('FISHR', (int) $barangay->id, $i) 
                    : null;
                $cloa = ($agencyName === 'DAR') 
                    ? $this->buildRegistryCode('DAR', (int) $barangay->id, $i) 
                    : null;

                $beneficiary = Beneficiary::create([
                    'agency_id' => $agency?->id,
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'name_suffix' => $nameSuffix,
                    'full_name' => $fullName,
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
                    'rsbsa_number' => $rsbsa,
                    'fishr_number' => $fishr,
                    'farm_ownership' => (str_contains($classification, 'Farmer')) ? fake()->randomElement(['Owner', 'Lessee', 'Share Tenant']) : null,
                    'farm_size_hectares' => (str_contains($classification, 'Farmer')) ? fake()->randomFloat(2, 0.25, 6.50) : null,
                    'primary_commodity' => (str_contains($classification, 'Farmer')) ? fake()->randomElement(['Rice', 'Corn', 'Vegetables', 'Sugarcane']) : null,
                    'farm_type' => (str_contains($classification, 'Farmer')) ? fake()->randomElement(['Irrigated', 'Rainfed Lowland', 'Upland']) : null,
                    'fisherfolk_type' => (str_contains($classification, 'Fisherfolk')) ? fake()->randomElement(['Capture Fishing', 'Fish Farming', 'Fish Vendor', 'Fish Worker']) : null,
                    'main_fishing_gear' => (str_contains($classification, 'Fisherfolk')) ? fake()->randomElement(['Gill Net', 'Hook and Line', 'Fish Trap', 'Cast Net']) : null,
                    'has_fishing_vessel' => (str_contains($classification, 'Fisherfolk')) ? fake()->boolean(40) : false,
                    'fishing_vessel_type' => (str_contains($classification, 'Fisherfolk')) ? fake()->randomElement(['Motorized', 'Non-Motorized']) : null,
                    'custom_fields' => ($agencyName === 'DAR' && $darAgencyId) ? json_encode([
                        'agency_dynamic' => [
                            (string)$darAgencyId => [
                                'cloa_ep_number' => $cloa,
                                'arb_classification' => fake()->randomElement(['ARBs', 'Potential ARBs']),
                                'landholding_description' => fake()->randomElement(['Irrigated rice land', 'Upland mixed crop area']),
                                'land_area_awarded_hectares' => fake()->randomFloat(2, 0.20, 5.00),
                                'ownership_scheme' => fake()->randomElement(['Individual', 'Collective']),
                                'barc_membership_status' => fake()->randomElement(['Member', 'Non-member']),
                            ]
                        ]
                    ]) : null,
                ]);

                if ($agency) {
                    $identifier = match ($agencyName) {
                        'DA' => $rsbsa,
                        'BFAR' => $fishr,
                        'DAR' => $cloa,
                        default => null
                    };

                    DB::table('beneficiary_agencies')->insert([
                        'beneficiary_id' => $beneficiary->id,
                        'agency_id' => $agency->id,
                        'identifier' => $identifier,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $created++;
            }
        }

        $this->command?->info("Created {$created} beneficiaries.");
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
