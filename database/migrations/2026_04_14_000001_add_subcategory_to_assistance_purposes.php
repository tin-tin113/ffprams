<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update existing categories to match new simplified structure
        DB::table('assistance_purposes')->where('category', 'agricultural')->update(['category' => 'production']);
        DB::table('assistance_purposes')->where('category', 'fishery')->update(['category' => 'production']);
        DB::table('assistance_purposes')->whereIn('category', ['livelihood', 'medical'])->where('category', '!=', 'emergency')->update(['category' => 'livelihood']);
        DB::table('assistance_purposes')->where('category', 'other')->update(['category' => 'emergency']);

        // Modify the category enum column
        Schema::table('assistance_purposes', function (Blueprint $table) {
            $table->string('category')->change(); // Convert enum to string for flexibility
            $table->string('type')->nullable()->after('category'); // Add type/subcategory column
        });

        // Re-add the enum constraint if you prefer - but string is more flexible
        // Alternatively, you can keep it as string or change to enum('production', 'livelihood', 'emergency')
    }

    public function down(): void
    {
        Schema::table('assistance_purposes', function (Blueprint $table) {
            $table->dropColumn('type');
            // Revert would be complex; skipping
        });
    }
};
