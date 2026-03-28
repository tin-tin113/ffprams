<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE beneficiaries MODIFY farm_ownership ENUM('Registered Owner', 'Tenant', 'Lessee', 'Owner', 'Share Tenant') NULL");
            DB::statement("ALTER TABLE beneficiaries MODIFY farm_type ENUM('Irrigated', 'Rainfed Upland', 'Rainfed Lowland', 'Upland') NULL");
            DB::statement("ALTER TABLE beneficiaries MODIFY fisherfolk_type ENUM('Capture Fishing', 'Aquaculture', 'Post-Harvest', 'Fish Farming', 'Fish Vendor', 'Fish Worker') NULL");
        }

        DB::statement('CREATE UNIQUE INDEX beneficiaries_cloa_ep_number_unique ON beneficiaries (cloa_ep_number)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX beneficiaries_cloa_ep_number_unique ON beneficiaries');

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE beneficiaries MODIFY farm_ownership ENUM('Owner', 'Lessee', 'Share Tenant') NULL");
            DB::statement("ALTER TABLE beneficiaries MODIFY farm_type ENUM('Irrigated', 'Rainfed Lowland', 'Upland') NULL");
            DB::statement("ALTER TABLE beneficiaries MODIFY fisherfolk_type ENUM('Capture Fishing', 'Fish Farming', 'Fish Vendor', 'Fish Worker') NULL");
        }
    }
};
