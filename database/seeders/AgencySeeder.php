<?php

namespace Database\Seeders;

use App\Models\Agency;
use Illuminate\Database\Seeder;

class AgencySeeder extends Seeder
{
    public function run(): void
    {
        // Primary partner agencies per FFPRAMS Reference Document
        // Additional agencies can be added by LGU Admin through Module 4
        $agencies = [
            ['name' => 'DA',   'full_name' => 'Department of Agriculture'],
            ['name' => 'BFAR', 'full_name' => 'Bureau of Fisheries and Aquatic Resources'],
            ['name' => 'DAR',  'full_name' => 'Department of Agrarian Reform'],
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
