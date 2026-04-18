<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barangays', function (Blueprint $table) {
            if (!Schema::hasColumn('barangays', 'municipality')) {
                $table->string('municipality')->default('E.B. Magalona')->after('name');
            }
            if (!Schema::hasColumn('barangays', 'province')) {
                $table->string('province')->default('Negros Occidental')->after('municipality');
            }

            // Check before adding index
            $indexExists = false;
            $indexes = DB::select("SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_NAME = 'barangays' AND COLUMN_NAME IN ('municipality', 'province')");
            if (empty($indexes)) {
                $table->index(['municipality', 'province']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('barangays', function (Blueprint $table) {
            $table->dropIndex(['municipality', 'province']);
            $table->dropColumn(['municipality', 'province']);
        });
    }
};
