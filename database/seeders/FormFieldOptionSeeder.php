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
        ];

        foreach ($groups as $fieldGroup => $options) {
            foreach ($options as $index => $label) {
                FormFieldOption::updateOrCreate(
                    ['field_group' => $fieldGroup, 'value' => $label],
                    [
                        'label'      => $label,
                        'sort_order' => ($index + 1) * 10,
                        'is_active'  => true,
                    ],
                );
            }
        }
    }
}
