<?php

namespace Database\Seeders;

use App\Models\Agency;
use Illuminate\Database\Seeder;

class AgencySeeder extends Seeder
{
    public function run(): void
    {
        $agencies = [
            ['name' => 'DA',   'full_name' => 'Department of Agriculture'],
            ['name' => 'BFAR', 'full_name' => 'Bureau of Fisheries and Aquatic Resources'],
            ['name' => 'DILG', 'full_name' => 'Department of Interior and Local Government'],
            ['name' => 'DICT', 'full_name' => 'Department of Information and Communications Technology'],
            ['name' => 'DSWD', 'full_name' => 'Department of Social Welfare and Development'],
            ['name' => 'DAR',  'full_name' => 'Department of Agrarian Reform'],
            ['name' => 'LGU',  'full_name' => 'Local Government Unit — Enrique B. Magalona'],
        ];

        foreach ($agencies as $agency) {
            Agency::updateOrCreate(
                ['name' => $agency['name']],
                [
                    'full_name' => $agency['full_name'],
                    'is_active' => true,
                ]
            );
        }
    }
}
