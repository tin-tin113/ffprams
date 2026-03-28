<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasNullProgram = DB::table('distribution_events')->whereNull('program_name_id')->exists();

        if ($hasNullProgram) {
            throw new RuntimeException('Cannot enforce required program_name_id: existing distribution_events rows have NULL program_name_id. Backfill those rows first.');
        }

        if (DB::getDriverName() === 'mysql') {
            Schema::table('distribution_events', function (Blueprint $table) {
                $table->dropForeign('distribution_events_program_name_id_foreign');
            });

            DB::statement('ALTER TABLE distribution_events MODIFY program_name_id BIGINT UNSIGNED NOT NULL');

            Schema::table('distribution_events', function (Blueprint $table) {
                $table->foreign('program_name_id')
                    ->references('id')
                    ->on('program_names')
                    ->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            Schema::table('distribution_events', function (Blueprint $table) {
                $table->dropForeign('distribution_events_program_name_id_foreign');
            });

            DB::statement('ALTER TABLE distribution_events MODIFY program_name_id BIGINT UNSIGNED NULL');

            Schema::table('distribution_events', function (Blueprint $table) {
                $table->foreign('program_name_id')
                    ->references('id')
                    ->on('program_names')
                    ->nullOnDelete();
            });
        }
    }
};
