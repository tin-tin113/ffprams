<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE resource_types MODIFY COLUMN source_agency ENUM('DA', 'BFAR', 'DAR', 'LGU', 'DSWD') DEFAULT 'LGU'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE resource_types MODIFY COLUMN source_agency ENUM('DA', 'BFAR', 'DAR', 'LGU') DEFAULT 'LGU'");
    }
};
