<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - remove DAR-specific fields from global form field options.
     * arb_classification and ownership_scheme are now managed as DAR agency-specific fields only.
     */
    public function up(): void
    {
        // Remove form field option entries for DAR-specific fields
        DB::table('form_field_options')
            ->whereIn('field_group', ['arb_classification', 'ownership_scheme'])
            ->delete();
    }

    /**
     * Rollback - restore DAR fields to global form field options.
     */
    public function down(): void
    {
        // Re-seed arb_classification options
        $arbOptions = [
            'Agricultural Lessee',
            'Regular Farmworker',
            'Seasonal Farmworker',
            'Other Farmworker',
            'Actual Tiller',
            'Collective/Cooperative',
            'Others',
        ];

        $sortOrder = 10;
        foreach ($arbOptions as $label) {
            DB::table('form_field_options')->updateOrCreate(
                ['field_group' => 'arb_classification', 'value' => strtolower(str_replace(' ', '_', $label))],
                [
                    'placement_section' => 'dar_information',
                    'label' => $label,
                    'sort_order' => $sortOrder,
                    'is_required' => true,
                    'is_active' => true,
                ],
            );
            $sortOrder += 10;
        }

        // Re-seed ownership_scheme options
        $ownershipOptions = ['Individual', 'Collective', 'Cooperative'];
        $sortOrder = 10;
        foreach ($ownershipOptions as $label) {
            DB::table('form_field_options')->updateOrCreate(
                ['field_group' => 'ownership_scheme', 'value' => strtolower(str_replace(' ', '_', $label))],
                [
                    'placement_section' => 'dar_information',
                    'label' => $label,
                    'sort_order' => $sortOrder,
                    'is_required' => true,
                    'is_active' => true,
                ],
            );
            $sortOrder += 10;
        }
    }
};
