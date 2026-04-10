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
                'PhilSys ID',
                "Voter's ID",
                "Driver's License",
                'Passport',
                'Senior Citizen ID',
                'PWD ID',
                'Postal ID',
                'TIN ID',
            ],

            // ── Highest Education ───────────────────
            'highest_education' => [
                'No Formal Education',
                'Elementary',
                'High School',
                'Vocational',
                'College',
                'Post Graduate',
            ],

            // ── Farm Type ───────────────────────────
            'farm_type' => [
                'Irrigated',
                'Rainfed Lowland',
                'Upland',
            ],

            // ── Farm Ownership ──────────────────────
            'farm_ownership' => [
                'Owner',
                'Lessee',
                'Share Tenant',
            ],

            // ── Fisherfolk Type ─────────────────────
            'fisherfolk_type' => [
                'Capture Fishing',
                'Fish Farming',
                'Fish Vendor',
                'Fish Worker',
            ],

            // ── Civil Status ────────────────────────
            'civil_status' => [
                'Single',
                'Married',
                'Widowed',
                'Separated',
            ],

            // ── ARB Classification ──────────────────
            'arb_classification' => [
                'Agricultural Lessee',
                'Regular Farmworker',
                'Seasonal Farmworker',
                'Other Farmworker',
                'Actual Tiller',
                'Collective/Cooperative',
                'Others',
            ],

            // ── Ownership Scheme ────────────────────
            'ownership_scheme' => [
                'Individual',
                'Collective',
                'Cooperative',
            ],
        ];

        foreach ($groups as $fieldGroup => $options) {
            foreach ($options as $index => $label) {
                FormFieldOption::updateOrCreate(
                    ['field_group' => $fieldGroup, 'value' => strtolower(str_replace(' ', '_', $label))],
                    [
                        'label'      => $label,
                        'sort_order' => ($index + 1) * 10,
                        'is_active'  => true,
                    ],
                );
            }
        }

        $this->command->info('Form field options seeded successfully (8 required groups).');
    }
}
