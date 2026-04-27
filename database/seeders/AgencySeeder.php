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
        Classification::updateOrCreate(
            ['name' => 'Farmer'],
            ['description' => 'Agricultural farmers and crop producers']
        );

        Classification::updateOrCreate(
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
                    ],
                    [
                        'field_name' => 'arb_classification',
                        'display_label' => 'ARB Classification',
                        'field_type' => 'dropdown',
                        'is_required' => false,
                        'form_section' => 'dar_information',
                        'sort_order' => 2,
                        'options' => [
                            'ARBs',
                            'Potential ARBs',
                            'Agricultural Lessee',
                            'Regular Farmworker',
                            'Seasonal Farmworker',
                            'Other Farmworker',
                            'Actual Tiller',
                            'Collective/Cooperative',
                            'Others',
                        ],
                    ],
                    [
                        'field_name' => 'landholding_description',
                        'display_label' => 'Landholding Description',
                        'field_type' => 'text',
                        'is_required' => false,
                        'form_section' => 'dar_information',
                        'sort_order' => 3,
                    ],
                    [
                        'field_name' => 'land_area_awarded_hectares',
                        'display_label' => 'Land Area Awarded (Hectares)',
                        'field_type' => 'decimal',
                        'is_required' => false,
                        'form_section' => 'dar_information',
                        'sort_order' => 4,
                    ],
                    [
                        'field_name' => 'ownership_scheme',
                        'display_label' => 'Ownership Scheme',
                        'field_type' => 'dropdown',
                        'is_required' => false,
                        'form_section' => 'dar_information',
                        'sort_order' => 5,
                        'options' => [
                            'Individual',
                            'Collective',
                            'Cooperative',
                        ],
                    ],
                    [
                        'field_name' => 'barc_membership_status',
                        'display_label' => 'BARC Membership Status',
                        'field_type' => 'text',
                        'is_required' => false,
                        'form_section' => 'dar_information',
                        'sort_order' => 6,
                    ],
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

            // Sync classifications dynamically
            $classificationIds = Classification::whereIn('name', $agencyData['classifications'])->pluck('id');
            $agency->classifications()->sync($classificationIds);

            // Create form fields
            foreach ($agencyData['form_fields'] as $fieldData) {
                $field = AgencyFormField::updateOrCreate(
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

                if (in_array($fieldData['field_type'], ['dropdown', 'checkbox'], true)) {
                    foreach (($fieldData['options'] ?? []) as $index => $label) {
                        $value = strtolower(preg_replace('/[^a-z0-9]+/', '_', trim((string) $label)));
                        $value = trim($value, '_');

                        if ($value === '') continue;

                        $field->options()->updateOrCreate(
                            ['value' => $value],
                            [
                                'label' => $label,
                                'sort_order' => ($index + 1) * 10,
                                'is_active' => true,
                            ],
                        );
                    }
                }
            }
        }
    }
}
