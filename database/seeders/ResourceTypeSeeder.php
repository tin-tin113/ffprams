<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResourceTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Resource Types per FFPRAMS Reference Document
        $types = [
            // DA Resource Types
            ['name' => 'Seeds',                  'unit' => 'kg',    'agency' => 'DA'],
            ['name' => 'Fertilizer',             'unit' => 'sacks', 'agency' => 'DA'],
            ['name' => 'Fuel Voucher',           'unit' => 'PHP',   'agency' => 'DA'],
            ['name' => 'Cash Grant',             'unit' => 'PHP',   'agency' => 'DA'],
            ['name' => 'Crop Insurance Premium', 'unit' => 'PHP',   'agency' => 'DA'],
            ['name' => 'Farm Equipment',         'unit' => 'units', 'agency' => 'DA'],

            // BFAR Resource Types
            ['name' => 'FRP Boat',               'unit' => 'units', 'agency' => 'BFAR'],
            ['name' => 'Fishing Gear',           'unit' => 'sets',  'agency' => 'BFAR'],
            ['name' => 'Fingerlings',            'unit' => 'pcs',   'agency' => 'BFAR'],
            ['name' => 'Aquaculture Starter Kit','unit' => 'sets',  'agency' => 'BFAR'],
            ['name' => 'Animal Feeds',           'unit' => 'kg',    'agency' => 'BFAR'],
            ['name' => 'Cold Storage Equipment', 'unit' => 'units', 'agency' => 'BFAR'],

            // DAR Resource Types
            ['name' => 'Farm Inputs',            'unit' => 'sets',  'agency' => 'DAR'],
            ['name' => 'Seedlings',              'unit' => 'pcs',   'agency' => 'DAR'],
            ['name' => 'Livelihood Equipment',   'unit' => 'units', 'agency' => 'DAR'],
            ['name' => 'Training Materials',     'unit' => 'sets',  'agency' => 'DAR'],
            ['name' => 'Cash Assistance',        'unit' => 'PHP',   'agency' => 'DAR'],
            ['name' => 'Infrastructure Support', 'unit' => 'PHP',   'agency' => 'DAR'],
        ];

        $agencies = DB::table('agencies')->pluck('id', 'name');

        foreach ($types as $type) {
            DB::table('resource_types')->updateOrInsert(
                ['name' => $type['name']],
                [
                    'unit'       => $type['unit'],
                    'agency_id'  => $agencies[$type['agency']] ?? null,
                    'is_active'  => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }
    }
}
