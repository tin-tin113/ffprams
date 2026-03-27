<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Convert ENUM to nullable VARCHAR to accept any agency name
        DB::statement("ALTER TABLE resource_types MODIFY COLUMN source_agency VARCHAR(255) NULL DEFAULT NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE resource_types MODIFY COLUMN source_agency ENUM('DA', 'BFAR', 'DAR', 'LGU', 'DSWD') DEFAULT 'LGU'");
    }
};
