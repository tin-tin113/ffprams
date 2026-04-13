<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add workflow status and distribution event linking to direct_assistance table.
     * Enables workflow tracking (planned → ready_for_release → released / not_received) and flexible
     * linking with batch distribution events.
     */
    public function up(): void
    {
        Schema::table('direct_assistance', function (Blueprint $table) {
            // Workflow status
            $table->enum('status', ['planned', 'ready_for_release', 'released', 'not_received'])
                ->default('planned')
                ->after('release_outcome');

            // Optional link to distribution event (batch processing)
            $table->foreignId('distribution_event_id')
                ->nullable()
                ->after('status')
                ->constrained('distribution_events')
                ->onDelete('set null');

            // Add index for status filtering
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('direct_assistance', function (Blueprint $table) {
            $table->dropForeign(['distribution_event_id']);
            $table->dropIndex(['status']);
            $table->dropColumn(['distribution_event_id', 'status']);
        });
    }
};
