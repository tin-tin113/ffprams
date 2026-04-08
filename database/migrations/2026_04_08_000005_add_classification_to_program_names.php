<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add classification field to program_names table.
     * Enables eligibility filtering based on beneficiary classification (Farmer, Fisherfolk, Both).
     */
    public function up(): void
    {
        Schema::table('program_names', function (Blueprint $table) {
            // Classification determines which beneficiary types can receive this program
            // Values: 'Farmer', 'Fisherfolk', 'Both'
            $table->enum('classification', ['Farmer', 'Fisherfolk', 'Both'])
                ->default('Both')
                ->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_names', function (Blueprint $table) {
            $table->dropColumn('classification');
        });
    }
};
