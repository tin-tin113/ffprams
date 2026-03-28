<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('allocations', function (Blueprint $table) {
            if (! Schema::hasColumn('allocations', 'release_method')) {
                $table->enum('release_method', ['event', 'direct'])
                    ->default('event')
                    ->after('distribution_event_id');
                $table->index('release_method');
            }

            if (! Schema::hasColumn('allocations', 'program_name_id')) {
                $table->foreignId('program_name_id')
                    ->nullable()
                    ->after('assistance_purpose_id')
                    ->constrained('program_names')
                    ->nullOnDelete();
                $table->index('program_name_id');
            }

            if (! Schema::hasColumn('allocations', 'resource_type_id')) {
                $table->foreignId('resource_type_id')
                    ->nullable()
                    ->after('program_name_id')
                    ->constrained('resource_types')
                    ->nullOnDelete();
                $table->index('resource_type_id');
            }
        });

        // Backfill direct workflow context from linked events for legacy rows.
        // SQLite does not reliably support joined update expressions used below.
        if (DB::getDriverName() === 'sqlite') {
            DB::table('allocations')
                ->whereNull('deleted_at')
                ->whereNull('release_method')
                ->update(['release_method' => 'event']);
        } elseif (Schema::hasTable('distribution_events')) {
            $updatePayload = [
                'allocations.release_method' => DB::raw("COALESCE(allocations.release_method, 'event')"),
            ];

            if (Schema::hasColumn('distribution_events', 'program_name_id')) {
                $updatePayload['allocations.program_name_id'] = DB::raw('COALESCE(allocations.program_name_id, distribution_events.program_name_id)');
            }

            if (Schema::hasColumn('distribution_events', 'resource_type_id')) {
                $updatePayload['allocations.resource_type_id'] = DB::raw('COALESCE(allocations.resource_type_id, distribution_events.resource_type_id)');
            }

            DB::table('allocations')
                ->join('distribution_events', 'allocations.distribution_event_id', '=', 'distribution_events.id')
                ->whereNull('allocations.deleted_at')
                ->update($updatePayload);
        } else {
            DB::table('allocations')
                ->whereNull('deleted_at')
                ->whereNull('release_method')
                ->update(['release_method' => 'event']);
        }

        // distribution_event_id must become nullable to support direct assistance.
        if (DB::getDriverName() === 'mysql') {
            Schema::table('allocations', function (Blueprint $table) {
                $table->dropForeign('allocations_distribution_event_id_foreign');
            });

            DB::statement('ALTER TABLE allocations MODIFY distribution_event_id BIGINT UNSIGNED NULL');

            Schema::table('allocations', function (Blueprint $table) {
                $table->foreign('distribution_event_id')
                    ->references('id')->on('distribution_events')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            Schema::table('allocations', function (Blueprint $table) {
                $table->dropForeign('allocations_distribution_event_id_foreign');
            });

            $hasNullEvent = DB::table('allocations')->whereNull('distribution_event_id')->exists();
            if (! $hasNullEvent) {
                DB::statement('ALTER TABLE allocations MODIFY distribution_event_id BIGINT UNSIGNED NOT NULL');
            } else {
                DB::statement('ALTER TABLE allocations MODIFY distribution_event_id BIGINT UNSIGNED NULL');
            }

            Schema::table('allocations', function (Blueprint $table) {
                $table->foreign('distribution_event_id')
                    ->references('id')->on('distribution_events')
                    ->nullOnDelete();
            });
        }

        Schema::table('allocations', function (Blueprint $table) {
            if (Schema::hasColumn('allocations', 'resource_type_id')) {
                $table->dropForeign(['resource_type_id']);
                $table->dropIndex(['resource_type_id']);
                $table->dropColumn('resource_type_id');
            }

            if (Schema::hasColumn('allocations', 'program_name_id')) {
                $table->dropForeign(['program_name_id']);
                $table->dropIndex(['program_name_id']);
                $table->dropColumn('program_name_id');
            }

            if (Schema::hasColumn('allocations', 'release_method')) {
                $table->dropIndex(['release_method']);
                $table->dropColumn('release_method');
            }
        });
    }
};
