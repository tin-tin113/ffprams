<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\ProgramName;
use Illuminate\Database\Seeder;

class ProgramNameSeeder extends Seeder
{
    public function run(): void
    {
        $programsByAgency = [
            'DA' => [
                'Rice Seed Program',
                'Corn Seed Program',
                'HVCC Seed Program',
                'Fertilizer Subsidy Program',
                'Fuel Subsidy Program',
                'RCEF-RFFA',
                'PCIC Premium Subsidy Program',
                'Farm Mechanization Program',
            ],
            'BFAR' => [
                'FRP Boat Distribution Program',
                'Fishing Gear Assistance Program',
                'Fingerling Dispersal Program',
                'Aquaculture Input Assistance',
                'Mariculture Development Program',
                'Post-Harvest Facility Program',
            ],
            'DAR' => [
                'ARBDSP-FPS (Farm Productivity Support)',
                'ARBDSP-EDES (Enterprise Development)',
                'ARBDSP-ARF (Access to Rural Finance)',
                'ARBDSP-SIBS (Social Infrastructure Building)',
            ],
        ];

        foreach ($programsByAgency as $agencyName => $programs) {
            $agency = Agency::where('name', $agencyName)->first();

            if (! $agency) {
                $this->command->warn("Agency '{$agencyName}' not found. Skipping its programs.");
                continue;
            }

            foreach ($programs as $programName) {
                ProgramName::updateOrCreate(
                    [
                        'agency_id' => $agency->id,
                        'name'      => $programName,
                    ],
                    [
                        'is_active' => true,
                    ]
                );
            }

            $this->command->info("Seeded " . count($programs) . " programs for {$agencyName}.");
        }
    }
}
