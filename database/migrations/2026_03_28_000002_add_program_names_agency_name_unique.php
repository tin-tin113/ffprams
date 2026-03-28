<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            $exists = DB::select("SHOW INDEX FROM program_names WHERE Key_name = 'program_names_agency_id_name_unique'");
            if (! empty($exists)) {
                return;
            }
        }

        Schema::table('program_names', function (Blueprint $table) {
            $table->unique(['agency_id', 'name'], 'program_names_agency_id_name_unique');
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            $exists = DB::select("SHOW INDEX FROM program_names WHERE Key_name = 'program_names_agency_id_name_unique'");
            if (empty($exists)) {
                return;
            }
        }

        Schema::table('program_names', function (Blueprint $table) {
            $table->dropUnique('program_names_agency_id_name_unique');
        });
    }
};
