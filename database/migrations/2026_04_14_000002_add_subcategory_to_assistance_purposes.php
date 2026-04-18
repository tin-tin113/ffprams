<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First modify the category column to allow string/text, then update values
        Schema::table('assistance_purposes', function (Blueprint $table) {
            $table->string('category')->change(); // Convert enum to string for flexibility
        });

        // Now update existing categories to match new simplified structure
        DB::table('assistance_purposes')->where('category', 'agricultural')->update(['category' => 'production']);
        DB::table('assistance_purposes')->where('category', 'fishery')->update(['category' => 'production']);
        DB::table('assistance_purposes')->whereIn('category', ['livelihood', 'medical'])->where('category', '!=', 'emergency')->update(['category' => 'livelihood']);
        DB::table('assistance_purposes')->where('category', 'other')->update(['category' => 'emergency']);

        // Add type/subcategory column
        Schema::table('assistance_purposes', function (Blueprint $table) {
            if (!Schema::hasColumn('assistance_purposes', 'type')) {
                $table->string('type')->nullable()->after('category');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assistance_purposes', function (Blueprint $table) {
            $table->dropColumn('type');
            // Revert would be complex; skipping
        });
    }
};
