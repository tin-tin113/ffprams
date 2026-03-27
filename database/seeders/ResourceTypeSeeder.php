<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResourceTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Resource Types per FFPRAMS Reference Document
        // DA Resource Types
        $types = [
            ['name' => 'Seeds',                  'unit' => 'kg',    'source_agency' => 'DA'],
            ['name' => 'Fertilizer',             'unit' => 'sacks', 'source_agency' => 'DA'],
            ['name' => 'Fuel Voucher',           'unit' => 'PHP',   'source_agency' => 'DA'],
            ['name' => 'Cash Grant',             'unit' => 'PHP',   'source_agency' => 'DA'],
            ['name' => 'Crop Insurance Premium', 'unit' => 'PHP',   'source_agency' => 'DA'],
            ['name' => 'Farm Equipment',         'unit' => 'units', 'source_agency' => 'DA'],

            // BFAR Resource Types
            ['name' => 'FRP Boat',               'unit' => 'units', 'source_agency' => 'BFAR'],
            ['name' => 'Fishing Gear',           'unit' => 'sets',  'source_agency' => 'BFAR'],
            ['name' => 'Fingerlings',            'unit' => 'pcs',   'source_agency' => 'BFAR'],
            ['name' => 'Aquaculture Starter Kit','unit' => 'sets',  'source_agency' => 'BFAR'],
            ['name' => 'Animal Feeds',           'unit' => 'kg',    'source_agency' => 'BFAR'],
            ['name' => 'Cold Storage Equipment', 'unit' => 'units', 'source_agency' => 'BFAR'],

            // DAR Resource Types
            ['name' => 'Farm Inputs',            'unit' => 'sets',  'source_agency' => 'DAR'],
            ['name' => 'Seedlings',              'unit' => 'pcs',   'source_agency' => 'DAR'],
            ['name' => 'Livelihood Equipment',   'unit' => 'units', 'source_agency' => 'DAR'],
            ['name' => 'Training Materials',     'unit' => 'sets',  'source_agency' => 'DAR'],
            ['name' => 'Cash Assistance',        'unit' => 'PHP',   'source_agency' => 'DAR'],
            ['name' => 'Infrastructure Support', 'unit' => 'PHP',   'source_agency' => 'DAR'],
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
