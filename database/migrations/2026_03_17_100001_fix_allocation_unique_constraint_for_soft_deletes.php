<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('allocations', function (Blueprint $table) {
            // Drop FKs that depend on the columns in the unique index
            $table->dropForeign('allocations_distribution_event_id_foreign');
            $table->dropForeign('allocations_beneficiary_id_foreign');
        });

        // Drop the old unique constraint
        DB::statement('ALTER TABLE allocations DROP INDEX allocation_event_beneficiary_unique');

        // Create a new unique index that includes deleted_at
        // In MySQL, NULL values are considered distinct in unique indexes,
        // so (event_id, beneficiary_id, NULL) won't conflict with another (event_id, beneficiary_id, NULL)
        // — we need a different approach. Instead, use a generated column.
        // Actually, MySQL treats multiple NULLs as distinct in unique indexes,
        // so this composite unique (event_id, beneficiary_id, deleted_at) will:
        //   - Allow multiple soft-deleted rows (deleted_at is non-null and unique per timestamp)
        //   - Allow one active row (deleted_at IS NULL) per event+beneficiary combo
        //   - BUT also allow multiple active rows if deleted_at is NULL (MySQL treats NULLs as distinct)
        // So we still need the application-level check, but the DB won't block re-allocation after soft-delete.
        DB::statement('CREATE UNIQUE INDEX allocation_event_beneficiary_unique ON allocations (distribution_event_id, beneficiary_id, deleted_at)');

        Schema::table('allocations', function (Blueprint $table) {
            // Re-add FKs
            $table->foreign('distribution_event_id')
                ->references('id')->on('distribution_events')
                ->cascadeOnDelete();
            $table->foreign('beneficiary_id')
                ->references('id')->on('beneficiaries')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('allocations', function (Blueprint $table) {
            $table->dropForeign('allocations_distribution_event_id_foreign');
            $table->dropForeign('allocations_beneficiary_id_foreign');
        });

        DB::statement('ALTER TABLE allocations DROP INDEX allocation_event_beneficiary_unique');
        DB::statement('CREATE UNIQUE INDEX allocation_event_beneficiary_unique ON allocations (distribution_event_id, beneficiary_id)');

        Schema::table('allocations', function (Blueprint $table) {
            $table->foreign('distribution_event_id')
                ->references('id')->on('distribution_events')
                ->cascadeOnDelete();
            $table->foreign('beneficiary_id')
                ->references('id')->on('beneficiaries')
                ->cascadeOnDelete();
        });
    }
};
