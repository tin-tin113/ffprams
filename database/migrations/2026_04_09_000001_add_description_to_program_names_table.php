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
        Schema::table('program_names', function (Blueprint $table) {
            // Add description column if it doesn't already exist
            if (!Schema::hasColumn('program_names', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_names', function (Blueprint $table) {
            if (Schema::hasColumn('program_names', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
