<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Backfill DAR/ARB agency-specific dropdown choices after DAR fields moved
     * out of the global form-field option table.
     */
    public function up(): void
    {
        $darAgencyId = DB::table('agencies')
            ->whereRaw('UPPER(name) = ?', ['DAR'])
            ->value('id');

        if (! $darAgencyId) {
            return;
        }

        $fields = [
            'cloa_ep_number' => [
                'display_label' => 'CLOA/EP Number',
                'field_type' => 'text',
                'sort_order' => 10,
            ],
            'arb_classification' => [
                'display_label' => 'ARB Classification',
                'field_type' => 'dropdown',
                'sort_order' => 20,
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
            'landholding_description' => [
                'display_label' => 'Landholding Description',
                'field_type' => 'text',
                'sort_order' => 30,
            ],
            'land_area_awarded_hectares' => [
                'display_label' => 'Land Area Awarded (Hectares)',
                'field_type' => 'decimal',
                'sort_order' => 40,
            ],
            'ownership_scheme' => [
                'display_label' => 'Ownership Scheme',
                'field_type' => 'dropdown',
                'sort_order' => 50,
                'options' => [
                    'Individual',
                    'Collective',
                    'Cooperative',
                ],
            ],
            'barc_membership_status' => [
                'display_label' => 'BARC Membership Status',
                'field_type' => 'text',
                'sort_order' => 60,
            ],
        ];

        foreach ($fields as $fieldName => $definition) {
            DB::table('agency_form_fields')->updateOrInsert(
                [
                    'agency_id' => $darAgencyId,
                    'field_name' => $fieldName,
                ],
                [
                    'display_label' => $definition['display_label'],
                    'field_type' => $definition['field_type'],
                    'is_required' => false,
                    'is_active' => true,
                    'form_section' => 'dar_information',
                    'sort_order' => $definition['sort_order'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );

            $fieldId = DB::table('agency_form_fields')
                ->where('agency_id', $darAgencyId)
                ->where('field_name', $fieldName)
                ->value('id');

            if (! $fieldId || empty($definition['options'])) {
                continue;
            }

            $this->seedOptions((int) $fieldId, $definition['options']);
        }
    }

    public function down(): void
    {
        $darAgencyId = DB::table('agencies')
            ->whereRaw('UPPER(name) = ?', ['DAR'])
            ->value('id');

        if (! $darAgencyId) {
            return;
        }

        $optionLabelsByField = [
            'arb_classification' => [
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
            'ownership_scheme' => [
                'Individual',
                'Collective',
                'Cooperative',
            ],
        ];

        foreach ($optionLabelsByField as $fieldName => $labels) {
            $fieldId = DB::table('agency_form_fields')
                ->where('agency_id', $darAgencyId)
                ->where('field_name', $fieldName)
                ->value('id');

            if (! $fieldId) {
                continue;
            }

            DB::table('agency_form_field_options')
                ->where('agency_form_field_id', $fieldId)
                ->whereIn('value', array_map(fn (string $label): string => $this->normalizeKey($label), $labels))
                ->delete();
        }
    }

    /**
     * @param  array<int, string>  $labels
     */
    private function seedOptions(int $fieldId, array $labels): void
    {
        $hasIsActive = Schema::hasColumn('agency_form_field_options', 'is_active');

        foreach (array_values($labels) as $index => $label) {
            $value = $this->normalizeKey($label);
            if ($value === '') {
                continue;
            }

            $values = [
                'label' => $label,
                'sort_order' => ($index + 1) * 10,
                'updated_at' => now(),
                'created_at' => now(),
            ];

            if ($hasIsActive) {
                $values['is_active'] = true;
            }

            DB::table('agency_form_field_options')->updateOrInsert(
                [
                    'agency_form_field_id' => $fieldId,
                    'value' => $value,
                ],
                $values,
            );
        }
    }

    private function normalizeKey(string $value): string
    {
        $normalized = strtolower(trim($value));
        $normalized = preg_replace('/[^a-z0-9]+/', '_', $normalized) ?? '';

        return trim($normalized, '_');
    }
};
