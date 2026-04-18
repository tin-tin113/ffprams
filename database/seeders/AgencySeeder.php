<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Classification;
use App\Models\AgencyFormField;
use Illuminate\Database\Seeder;

class AgencySeeder extends Seeder
{
    public function run(): void
    {
        // Seed classifications first
        $farmerClass = Classification::firstOrCreate(
            ['name' => 'Farmer'],
            ['description' => 'Agricultural farmers and crop producers']
        );

        $fisherfoldClass = Classification::firstOrCreate(
            ['name' => 'Fisherfolk'],
            ['description' => 'Fisherfolk and aquaculture practitioners']
        );

        // Primary partner agencies per FFPRAMS Reference Document
        $agencies = [
            [
                'name' => 'DA',
                'full_name' => 'Department of Agriculture',
                'classifications' => ['Farmer', 'Fisherfolk'],
                'form_fields' => [
                    [
                        'field_name' => 'rsbsa_number',
                        'display_label' => 'RSBSA Number',
                        'field_type' => 'text',
                        'is_required' => false,
                        'form_section' => 'farmer_information',
                        'sort_order' => 1,
                    ]
                ]
            ],
            [
                'name' => 'BFAR',
                'full_name' => 'Bureau of Fisheries and Aquatic Resources',
                'classifications' => ['Fisherfolk'],
                'form_fields' => [
                    [
                        'field_name' => 'fishr_number',
                        'display_label' => 'FishR Certificate Number',
                        'field_type' => 'text',
                        'is_required' => false,
                        'form_section' => 'fisherfolk_information',
                        'sort_order' => 1,
                    ]
                ]
            ],
            [
                'name' => 'DAR',
                'full_name' => 'Department of Agrarian Reform',
                'classifications' => ['Farmer'],
                'form_fields' => [
                    [
                        'field_name' => 'cloa_ep_number',
                        'display_label' => 'CLOA/EP Number',
                        'field_type' => 'text',
                        'is_required' => true,
                        'form_section' => 'dar_information',
                        'sort_order' => 1,
                    ]
                ]
            ],
        ];

        foreach ($agencies as $agencyData) {
            $agency = Agency::updateOrCreate(
                ['name' => $agencyData['name']],
                [
                    'full_name' => $agencyData['full_name'],
                    'is_active' => true,
                ]
            );

            // Sync classifications
            $classificationIds = [];
            foreach ($agencyData['classifications'] as $classificationName) {
                $classification = $agencyData['name'] === 'DA' && $classificationName === 'Farmer'
                    ? $farmerClass
                    : ($agencyData['name'] === 'DA' && $classificationName === 'Fisherfolk'
                        ? $fisherfoldClass
                        : ($agencyData['name'] === 'BFAR'
                            ? $fisherfoldClass
                            : $farmerClass));

                $classificationIds[] = $classification->id;
            }

            $agency->classifications()->sync($classificationIds);

            // Create form fields if they don't exist
            foreach ($agencyData['form_fields'] as $fieldData) {
                AgencyFormField::updateOrCreate(
                    [
                        'agency_id' => $agency->id,
                        'field_name' => $fieldData['field_name'],
                    ],
                    [
                        'display_label' => $fieldData['display_label'],
                        'field_type' => $fieldData['field_type'],
                        'is_required' => $fieldData['is_required'],
                        'is_active' => true,
                        'form_section' => $fieldData['form_section'],
                        'sort_order' => $fieldData['sort_order'],
                    ]
                );
            }
        }
    }
}
