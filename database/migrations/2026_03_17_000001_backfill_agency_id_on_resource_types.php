<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill agency_id on resource_types by matching source_agency to agencies.name
        $agencies = DB::table('agencies')->pluck('id', 'name');

        foreach ($agencies as $name => $id) {
            DB::table('resource_types')
                ->where('source_agency', $name)
                ->whereNull('agency_id')
                ->update(['agency_id' => $id]);
        }
    }

    public function down(): void
    {
        DB::table('resource_types')->update(['agency_id' => null]);
    }
};
