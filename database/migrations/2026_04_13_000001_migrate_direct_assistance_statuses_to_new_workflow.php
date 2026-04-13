<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('direct_assistance') || ! Schema::hasColumn('direct_assistance', 'status')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // Transitional enum keeps legacy and new values so existing rows can be migrated safely.
            DB::statement("ALTER TABLE direct_assistance MODIFY COLUMN status ENUM('recorded','distributed','completed','planned','ready_for_release','released','not_received') NOT NULL DEFAULT 'recorded'");
        }

        DB::table('direct_assistance')
            ->whereIn('status', ['recorded', ''])
            ->update(['status' => 'planned']);

        DB::table('direct_assistance')
            ->whereIn('status', ['distributed', 'completed'])
            ->update(['status' => 'released']);

        DB::table('direct_assistance')
            ->where('release_outcome', 'not_received')
            ->update(['status' => 'not_received']);

        DB::table('direct_assistance')
            ->whereNull('status')
            ->update(['status' => 'planned']);

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE direct_assistance MODIFY COLUMN status ENUM('planned','ready_for_release','released','not_received') NOT NULL DEFAULT 'planned'");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('direct_assistance') || ! Schema::hasColumn('direct_assistance', 'status')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver !== 'mysql') {
            // Non-MySQL rollback keeps current statuses because enum/check conversion is engine-specific.
            return;
        }

        DB::statement("ALTER TABLE direct_assistance MODIFY COLUMN status ENUM('recorded','distributed','completed','planned','ready_for_release','released','not_received') NOT NULL DEFAULT 'planned'");

        DB::table('direct_assistance')
            ->whereIn('status', ['planned', 'ready_for_release', 'not_received'])
            ->update(['status' => 'recorded']);

        DB::table('direct_assistance')
            ->where('status', 'released')
            ->update(['status' => 'distributed']);

        DB::statement("ALTER TABLE direct_assistance MODIFY COLUMN status ENUM('recorded','distributed','completed') NOT NULL DEFAULT 'recorded'");
    }
};
