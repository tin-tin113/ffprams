<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Cleanup agencies to keep only DA, BFAR, DAR as primary partner agencies.
 * Other agencies (DILG, DICT, DSWD, LGU) are deactivated per FFPRAMS Reference Document.
 * Additional agencies can be added by LGU Admin through Module 4 (System Settings).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Deactivate agencies that are not primary partners
        DB::table('agencies')
            ->whereNotIn('name', ['DA', 'BFAR', 'DAR'])
            ->update(['is_active' => false]);
    }

    public function down(): void
    {
        // Reactivate all agencies
        DB::table('agencies')
            ->update(['is_active' => true]);
    }
};
