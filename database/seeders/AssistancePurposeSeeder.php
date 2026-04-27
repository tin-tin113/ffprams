<?php

namespace Database\Seeders;

use App\Models\AssistancePurpose;
use Illuminate\Database\Seeder;

class AssistancePurposeSeeder extends Seeder
{
    public function run(): void
    {
        $purposes = [
            // Production
            ['name' => 'Purchase of seeds and fertilizer', 'category' => 'production', 'type' => 'Agricultural'],
            ['name' => 'Crop rehabilitation',              'category' => 'production', 'type' => 'Agricultural'],
            ['name' => 'Farm equipment repair',            'category' => 'production', 'type' => 'Agricultural'],
            ['name' => 'Livestock acquisition',            'category' => 'production', 'type' => 'Agricultural'],
            ['name' => 'Irrigation support',               'category' => 'production', 'type' => 'Agricultural'],
            ['name' => 'Purchase of fishing nets',          'category' => 'production', 'type' => 'Fishery'],
            ['name' => 'Fishing boat repair',               'category' => 'production', 'type' => 'Fishery'],
            ['name' => 'Purchase of fishing gear',          'category' => 'production', 'type' => 'Fishery'],
            ['name' => 'Aquaculture support',               'category' => 'production', 'type' => 'Fishery'],
            ['name' => 'Fingerlings acquisition',           'category' => 'production', 'type' => 'Fishery'],

            // Livelihood
            ['name' => 'Livelihood startup capital', 'category' => 'livelihood', 'type' => 'Startup'],
            ['name' => 'Small business support',     'category' => 'livelihood', 'type' => 'Expansion'],
            ['name' => 'Medical emergency assistance', 'category' => 'livelihood', 'type' => 'Medical'],
            ['name' => 'Hospitalization support',      'category' => 'livelihood', 'type' => 'Medical'],

            // Emergency
            ['name' => 'Calamity relief assistance', 'category' => 'emergency', 'type' => 'Natural Disaster'],
            ['name' => 'Disaster recovery support',  'category' => 'emergency', 'type' => 'Natural Disaster'],
            ['name' => 'General financial assistance', 'category' => 'emergency', 'type' => 'Financial Crisis'],
            ['name' => 'Educational assistance',       'category' => 'emergency', 'type' => 'Educational'],
        ];

        foreach ($purposes as $purpose) {
            AssistancePurpose::updateOrCreate(
                ['name' => $purpose['name']],
                [
                    'category'  => $purpose['category'],
                    'type'      => $purpose['type'] ?? null,
                    'is_active' => true,
                ]
            );
        }
    }
}
