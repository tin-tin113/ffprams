<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('allocations', function (Blueprint $table) {
            // Make distribution_event_id nullable for standalone allocations
            $table->foreignId('distribution_event_id')->nullable()->change();

            // Track source of record
            if (!Schema::hasColumn('allocations', 'legacy_id')) {
                $table->unsignedBigInteger('legacy_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('allocations', 'legacy_source')) {
                $table->string('legacy_source')->nullable()->after('legacy_id');
            }

            // Distinguish between event-based and standalone
            if (!Schema::hasColumn('allocations', 'release_method')) {
                $table->string('release_method')->default('event')->after('legacy_source');
            }

            // Administrative metadata from direct_assistance table
            if (!Schema::hasColumn('allocations', 'created_by')) {
                $table->foreignId('created_by')
                    ->nullable()
                    ->after('remarks')
                    ->constrained('users')
                    ->onDelete('set null');
            }
            if (!Schema::hasColumn('allocations', 'distributed_by')) {
                $table->foreignId('distributed_by')
                    ->nullable()
                    ->after('created_by')
                    ->constrained('users')
                    ->onDelete('set null');
            }

            // Amount field (ensuring it exists and has enough precision)
            if (!Schema::hasColumn('allocations', 'amount')) {
                $table->decimal('amount', 12, 2)->nullable()->after('quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allocations', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['distributed_by']);
            $table->dropColumn(['legacy_id', 'legacy_source', 'created_by', 'distributed_by', 'amount']);
        });
    }
};
