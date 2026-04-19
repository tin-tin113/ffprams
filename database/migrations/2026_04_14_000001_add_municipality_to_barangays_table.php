<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

            if (! Schema::hasIndex('barangays', ['municipality', 'province'])) {
                $table->index(['municipality', 'province']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('barangays', function (Blueprint $table) {
            if (Schema::hasColumn('barangays', 'municipality') && Schema::hasColumn('barangays', 'province')) {
                if (Schema::hasIndex('barangays', ['municipality', 'province'])) {
                    $table->dropIndex(['municipality', 'province']);
                }

                $table->dropColumn(['municipality', 'province']);
            }
        });
    }
};
