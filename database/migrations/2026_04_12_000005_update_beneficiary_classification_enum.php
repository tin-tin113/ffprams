<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Remove 'Both' from beneficiary classification enum, replacing all 'Both'
     * records with 'Farmer' for data preservation (conserves DA/primary eligibility data).
     */
    public function up(): void
    {
        // Update any existing 'Both' classification records to 'Farmer'
        // This preserves the primary agency data (DA/RSBSA)
        DB::table('beneficiaries')
            ->where('classification', 'Both')
            ->update(['classification' => 'Farmer']);

        // Modify the classification column to only allow 'Farmer' or 'Fisherfolk'
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->enum('classification', ['Farmer', 'Fisherfolk'])
                ->change()
                ->default('Farmer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the 'Both' option in the enum
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->enum('classification', ['Farmer', 'Fisherfolk', 'Both'])
                ->change()
                ->default('Farmer');
        });
    }
};
