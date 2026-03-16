<?php

namespace Database\Seeders;

use App\Models\AssistancePurpose;
use Illuminate\Database\Seeder;

class AssistancePurposeSeeder extends Seeder
{
    public function run(): void
    {
        $purposes = [
            // Agricultural
            ['name' => 'Purchase of seeds and fertilizer', 'category' => 'agricultural'],
            ['name' => 'Crop rehabilitation',              'category' => 'agricultural'],
            ['name' => 'Farm equipment repair',            'category' => 'agricultural'],
            ['name' => 'Livestock acquisition',            'category' => 'agricultural'],
            ['name' => 'Irrigation support',               'category' => 'agricultural'],

            // Fishery
            ['name' => 'Purchase of fishing nets',  'category' => 'fishery'],
            ['name' => 'Fishing boat repair',       'category' => 'fishery'],
            ['name' => 'Purchase of fishing gear',  'category' => 'fishery'],
            ['name' => 'Aquaculture support',       'category' => 'fishery'],
            ['name' => 'Fingerlings acquisition',   'category' => 'fishery'],

            // Livelihood
            ['name' => 'Livelihood startup capital', 'category' => 'livelihood'],
            ['name' => 'Small business support',     'category' => 'livelihood'],

            // Medical
            ['name' => 'Medical emergency assistance', 'category' => 'medical'],
            ['name' => 'Hospitalization support',      'category' => 'medical'],

            // Emergency
            ['name' => 'Calamity relief assistance', 'category' => 'emergency'],
            ['name' => 'Disaster recovery support',  'category' => 'emergency'],

            // Other
            ['name' => 'General financial assistance', 'category' => 'other'],
            ['name' => 'Educational assistance',       'category' => 'other'],
        ];

        foreach ($purposes as $purpose) {
            AssistancePurpose::updateOrCreate(
                ['name' => $purpose['name']],
                [
                    'category'  => $purpose['category'],
                    'is_active' => true,
                ]
            );
        }
    }
}
