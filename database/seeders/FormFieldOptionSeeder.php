<?php

namespace Database\Seeders;

use App\Models\FormFieldOption;
use Illuminate\Database\Seeder;

class FormFieldOptionSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            // ── Government ID Types ─────────────────
            'id_type' => [
                'placement_section' => FormFieldOption::PLACEMENT_PERSONAL_INFORMATION,
                'is_required' => false,
                'options' => [
                'PhilSys ID',
                "Voter's ID",
                "Driver's License",
                'Passport',
                'Senior Citizen ID',
                'PWD ID',
                'Postal ID',
                'TIN ID',
                ],
            ],

            // ── Highest Education ───────────────────
            'highest_education' => [
                'placement_section' => FormFieldOption::PLACEMENT_PERSONAL_INFORMATION,
                'is_required' => false,
                'options' => [
                'No Formal Education',
                'Elementary',
                'High School',
                'Vocational',
                'College',
                'Post Graduate',
                ],
            ],

            // ── Farm Type ───────────────────────────
            'farm_type' => [
                'placement_section' => FormFieldOption::PLACEMENT_FARMER_INFORMATION,
                'is_required' => true,
                'options' => [
                'Irrigated',
                'Rainfed Lowland',
                'Upland',
                ],
            ],

            // ── Farm Ownership ──────────────────────
            'farm_ownership' => [
                'placement_section' => FormFieldOption::PLACEMENT_FARMER_INFORMATION,
                'is_required' => true,
                'options' => [
                'Owner',
                'Lessee',
                'Share Tenant',
                ],
            ],

            // ── Fisherfolk Type ─────────────────────
            'fisherfolk_type' => [
                'placement_section' => FormFieldOption::PLACEMENT_FISHERFOLK_INFORMATION,
                'is_required' => true,
                'options' => [
                'Capture Fishing',
                'Fish Farming',
                'Fish Vendor',
                'Fish Worker',
                ],
            ],

            // ── Civil Status ────────────────────────
            'civil_status' => [
                'placement_section' => FormFieldOption::PLACEMENT_PERSONAL_INFORMATION,
                'is_required' => true,
                'options' => [
                'Single',
                'Married',
                'Widowed',
                'Separated',
                ],
            ],

            ];

        foreach ($groups as $fieldGroup => $groupConfig) {
            $options = $groupConfig['options'] ?? [];

            foreach ($options as $index => $label) {
                FormFieldOption::updateOrCreate(
                    ['field_group' => $fieldGroup, 'value' => strtolower(str_replace(' ', '_', $label))],
                    [
                        'placement_section' => $groupConfig['placement_section'] ?? FormFieldOption::PLACEMENT_PERSONAL_INFORMATION,
                        'label'      => $label,
                        'sort_order' => ($index + 1) * 10,
                        'is_required' => (bool) ($groupConfig['is_required'] ?? false),
                        'is_active'  => true,
                    ],
                );
            }
        }

        $this->command->info('Form field options seeded successfully (8 required groups).');
    }
}
