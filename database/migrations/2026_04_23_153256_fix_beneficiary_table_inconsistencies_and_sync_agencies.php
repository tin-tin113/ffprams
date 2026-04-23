<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Fix strict ENUMs to allow dynamic options from the UI
        Schema::table('beneficiaries', function (Blueprint $table) {
            DB::statement('ALTER TABLE beneficiaries MODIFY sex VARCHAR(255) NULL');
            DB::statement('ALTER TABLE beneficiaries MODIFY classification VARCHAR(255) NOT NULL DEFAULT "Farmer"');
            DB::statement('ALTER TABLE beneficiaries MODIFY status VARCHAR(255) NOT NULL DEFAULT "Active"');
        });

        // 2. Sync agency_id from main table to the beneficiary_agencies pivot table
        // This ensures data consistency between the single-agency (legacy) and multi-agency systems.
        DB::statement("
            INSERT INTO beneficiary_agencies (beneficiary_id, agency_id, registered_at, created_at, updated_at)
            SELECT id, agency_id, registered_at, NOW(), NOW()
            FROM beneficiaries
            WHERE agency_id IS NOT NULL
            ON DUPLICATE KEY UPDATE updated_at = NOW()
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            DB::statement("ALTER TABLE beneficiaries MODIFY sex ENUM('Male', 'Female') NULL");
            DB::statement("ALTER TABLE beneficiaries MODIFY classification ENUM('Farmer', 'Fisherfolk') NOT NULL DEFAULT 'Farmer'");
            DB::statement("ALTER TABLE beneficiaries MODIFY status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active'");
        });
    }
};
