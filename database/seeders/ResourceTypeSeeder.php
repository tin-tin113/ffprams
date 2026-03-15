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
        ];

        foreach ($types as $type) {
            DB::table('resource_types')->updateOrInsert(
                ['name' => $type['name']],
                [
                    'unit'          => $type['unit'],
                    'source_agency' => $type['source_agency'],
                    'updated_at'    => now(),
                    'created_at'    => now(),
                ],
            );
        }
    }
}
