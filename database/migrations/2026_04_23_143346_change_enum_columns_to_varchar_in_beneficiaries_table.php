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
        Schema::table('beneficiaries', function (Blueprint $table) {
            DB::statement('ALTER TABLE beneficiaries MODIFY farm_ownership VARCHAR(255) NULL');
            DB::statement('ALTER TABLE beneficiaries MODIFY farm_type VARCHAR(255) NULL');
            DB::statement('ALTER TABLE beneficiaries MODIFY fisherfolk_type VARCHAR(255) NULL');
            DB::statement('ALTER TABLE beneficiaries MODIFY civil_status VARCHAR(255) NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            // Reverting to ENUMs could cause data loss if new options were added, so we just leave them as VARCHAR or map them back.
            // But for safety, we'll recreate the original ENUMs if needed.
            // Note: If you have existing custom string values in these columns, running this 'down' will fail on MySQL strict mode.
            DB::statement("ALTER TABLE beneficiaries MODIFY farm_ownership ENUM('Registered Owner','Tenant','Lessee','Owner','Share Tenant') NULL");
            DB::statement("ALTER TABLE beneficiaries MODIFY farm_type ENUM('Irrigated','Rainfed Upland','Rainfed Lowland','Upland') NULL");
            DB::statement("ALTER TABLE beneficiaries MODIFY fisherfolk_type ENUM('Capture Fishing','Aquaculture','Post-Harvest','Fish Farming','Fish Vendor','Fish Worker') NULL");
            DB::statement("ALTER TABLE beneficiaries MODIFY civil_status ENUM('Single','Married','Widowed','Separated') NULL");
        });
    }
};
