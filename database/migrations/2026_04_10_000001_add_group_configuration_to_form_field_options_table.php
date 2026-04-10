<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_field_options', function (Blueprint $table) {
            if (! Schema::hasColumn('form_field_options', 'placement_section')) {
                $table->string('placement_section', 50)
                    ->default('personal_information')
                    ->after('field_group');
            }

            if (! Schema::hasColumn('form_field_options', 'is_required')) {
                $table->boolean('is_required')
                    ->default(false)
                    ->after('sort_order');
            }
        });

        $groupDefaults = [
            'civil_status' => ['placement' => 'personal_information', 'required' => true],
            'highest_education' => ['placement' => 'personal_information', 'required' => false],
            'id_type' => ['placement' => 'personal_information', 'required' => false],
            'farm_ownership' => ['placement' => 'farmer_information', 'required' => true],
            'farm_type' => ['placement' => 'farmer_information', 'required' => true],
            'fisherfolk_type' => ['placement' => 'fisherfolk_information', 'required' => true],
            'arb_classification' => ['placement' => 'dar_information', 'required' => true],
            'ownership_scheme' => ['placement' => 'dar_information', 'required' => true],
        ];

        foreach ($groupDefaults as $group => $defaults) {
            DB::table('form_field_options')
                ->where('field_group', $group)
                ->update([
                    'placement_section' => $defaults['placement'],
                    'is_required' => $defaults['required'],
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('form_field_options', function (Blueprint $table) {
            $dropColumns = [];

            if (Schema::hasColumn('form_field_options', 'placement_section')) {
                $dropColumns[] = 'placement_section';
            }

            if (Schema::hasColumn('form_field_options', 'is_required')) {
                $dropColumns[] = 'is_required';
            }

            if (! empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
