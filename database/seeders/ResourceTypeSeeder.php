<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResourceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            // DA Resources
            ['name' => 'Certified Inbred Rice Seeds', 'unit' => 'kg',    'source_agency' => 'DA'],
            ['name' => 'Corn Seeds',                  'unit' => 'kg',    'source_agency' => 'DA'],
            ['name' => 'Fertilizer (Inorganic)',      'unit' => 'sacks', 'source_agency' => 'DA'],
            ['name' => 'Fertilizer (Organic)',        'unit' => 'sacks', 'source_agency' => 'DA'],
            ['name' => 'Farm Equipment',              'unit' => 'units', 'source_agency' => 'DA'],
            ['name' => 'Livestock / Poultry',         'unit' => 'heads', 'source_agency' => 'DA'],

            // BFAR Resources
            ['name' => 'Fishing Net',                 'unit' => 'pcs',   'source_agency' => 'BFAR'],
            ['name' => 'Fishing Gear Set',            'unit' => 'sets',  'source_agency' => 'BFAR'],
            ['name' => 'Life Vest',                   'unit' => 'pcs',   'source_agency' => 'BFAR'],
            ['name' => 'Fingerlings',                 'unit' => 'pcs',   'source_agency' => 'BFAR'],
            ['name' => 'Fishing Boat Assistance',     'unit' => 'units', 'source_agency' => 'BFAR'],

            // LGU Financial Assistance
            ['name' => 'Cash Ayuda',                  'unit' => 'PHP',   'source_agency' => 'LGU'],
            ['name' => 'Calamity Assistance',         'unit' => 'PHP',   'source_agency' => 'LGU'],
            ['name' => 'Livelihood Subsidy',          'unit' => 'PHP',   'source_agency' => 'LGU'],
            ['name' => 'Educational Assistance',      'unit' => 'PHP',   'source_agency' => 'LGU'],
            ['name' => 'Medical Assistance',          'unit' => 'PHP',   'source_agency' => 'LGU'],

            // DSWD Financial Assistance
            ['name' => '4Ps Supplemental Aid',        'unit' => 'PHP',   'source_agency' => 'DSWD'],
            ['name' => 'AICS (Assistance to Individuals in Crisis Situation)', 'unit' => 'PHP', 'source_agency' => 'DSWD'],

            // DA Financial Programs
            ['name' => 'SURE-Aid (Survival and Recovery Assistance)', 'unit' => 'PHP', 'source_agency' => 'DA'],
            ['name' => 'Rice Farmer Financial Assistance (RFFA)',     'unit' => 'PHP', 'source_agency' => 'DA'],
        ];

        // Build agency name => id lookup
        $agencies = DB::table('agencies')->pluck('id', 'name');

        foreach ($types as $type) {
            DB::table('resource_types')->updateOrInsert(
                ['name' => $type['name']],
                [
                    'unit'          => $type['unit'],
                    'source_agency' => $type['source_agency'],
                    'agency_id'     => $agencies[$type['source_agency']] ?? null,
                    'updated_at'    => now(),
                    'created_at'    => now(),
                ],
            );
        }
    }
}
