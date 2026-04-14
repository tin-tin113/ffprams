<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drop the redundant `source_agency` column from resource_types.
 *
 * This column was superseded by the `agency_id` FK (added in
 * 2026_03_16_000005_add_agency_id_to_resource_types_table). The model's
 * saving() hook kept both in sync, but agency_id is the canonical reference.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resource_types', function (Blueprint $table) {
            $table->dropColumn('source_agency');
        });
    }

    public function down(): void
    {
        Schema::table('resource_types', function (Blueprint $table) {
            $table->string('source_agency')->nullable()->after('description');
        });
    }
};
