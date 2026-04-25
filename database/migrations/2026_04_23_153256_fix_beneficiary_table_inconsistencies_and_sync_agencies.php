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
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('beneficiaries', function (Blueprint $table) {
                $table->string('sex')->nullable()->change();
                $table->string('classification')->default('Farmer')->change();
                $table->string('status')->default('Active')->change();
            });
        } else {
            DB::statement('ALTER TABLE beneficiaries MODIFY sex VARCHAR(255) NULL');
            DB::statement('ALTER TABLE beneficiaries MODIFY classification VARCHAR(255) NOT NULL DEFAULT "Farmer"');
            DB::statement('ALTER TABLE beneficiaries MODIFY status VARCHAR(255) NOT NULL DEFAULT "Active"');
        }

        // 2. Sync agency_id and identifiers from main table to the beneficiary_agencies pivot table
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                INSERT INTO beneficiary_agencies (beneficiary_id, agency_id, identifier, registered_at, created_at, updated_at)
                SELECT id, agency_id, 
                       CASE 
                           WHEN agency_id = 1 THEN rsbsa_number 
                           WHEN agency_id = 2 THEN fishr_number 
                           ELSE NULL 
                       END,
                       registered_at, NOW(), NOW()
                FROM beneficiaries
                WHERE agency_id IS NOT NULL
                ON DUPLICATE KEY UPDATE 
                    identifier = VALUES(identifier),
                    updated_at = NOW()
            ");
        } else {
            // SQLite equivalent
            DB::statement("
                INSERT INTO beneficiary_agencies (beneficiary_id, agency_id, identifier, registered_at, created_at, updated_at)
                SELECT id, agency_id, 
                       CASE 
                           WHEN agency_id = 1 THEN rsbsa_number 
                           WHEN agency_id = 2 THEN fishr_number 
                           ELSE NULL 
                       END,
                       registered_at, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                FROM beneficiaries
                WHERE agency_id IS NOT NULL
                ON CONFLICT(beneficiary_id, agency_id) DO UPDATE SET 
                    identifier = EXCLUDED.identifier,
                    updated_at = CURRENT_TIMESTAMP
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // Reverting to ENUMs is complex in SQLite
        } else {
            Schema::table('beneficiaries', function (Blueprint $table) {
                DB::statement("ALTER TABLE beneficiaries MODIFY sex ENUM('Male', 'Female') NULL");
                DB::statement("ALTER TABLE beneficiaries MODIFY classification ENUM('Farmer', 'Fisherfolk') NOT NULL DEFAULT 'Farmer'");
                DB::statement("ALTER TABLE beneficiaries MODIFY status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active'");
            });
        }
    }
};
