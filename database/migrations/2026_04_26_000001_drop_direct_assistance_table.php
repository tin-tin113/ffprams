<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the direct_assistance table. All rows were already backfilled into
     * `allocations` (with legacy_source = 'direct_assistance') by migration
     * 2026_04_25_170000_backfill_direct_assistance_into_allocations.
     */
    public function up(): void
    {
        Schema::dropIfExists('direct_assistance');
    }

    /**
     * Recreate the original schema (creation + status/event additions) so a
     * rollback restores structure. Data is not restored.
     */
    public function down(): void
    {
        Schema::create('direct_assistance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')
                ->constrained('beneficiaries')
                ->onDelete('restrict');
            $table->foreignId('program_name_id')
                ->constrained('program_names')
                ->onDelete('restrict');
            $table->foreignId('resource_type_id')
                ->constrained('resource_types')
                ->onDelete('restrict');
            $table->foreignId('assistance_purpose_id')
                ->nullable()
                ->constrained('assistance_purposes')
                ->onDelete('set null');

            $table->decimal('quantity', 10, 2)->nullable();
            $table->decimal('amount', 12, 2)->nullable();

            $table->timestamp('distributed_at')->nullable();
            $table->enum('release_outcome', [
                'accepted',
                'partially_received',
                'refused',
                'not_found',
                'deferred',
            ])->nullable();

            $table->enum('status', ['planned', 'ready_for_release', 'released', 'not_received'])
                ->default('planned');
            $table->foreignId('distribution_event_id')
                ->nullable()
                ->constrained('distribution_events')
                ->onDelete('set null');

            $table->text('remarks')->nullable();
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('restrict');
            $table->foreignId('distributed_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            $table->index('beneficiary_id');
            $table->index('program_name_id');
            $table->index('resource_type_id');
            $table->index('created_by');
            $table->index('distributed_at');
            $table->index('status');
        });
    }
};
