<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('agency_form_field_options', function (Blueprint $table) {
            if (! Schema::hasColumn('agency_form_field_options', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('sort_order');
                $table->index('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agency_form_field_options', function (Blueprint $table) {
            if (Schema::hasColumn('agency_form_field_options', 'is_active')) {
                $table->dropIndex(['is_active']);
                $table->dropColumn('is_active');
            }
        });
    }
};
