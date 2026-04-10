<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Beneficiary list filters and dashboard rollups.
        $this->addIndex('beneficiaries', ['status', 'agency_id', 'barangay_id'], 'beneficiaries_status_agency_barangay_idx');
        $this->addIndex('beneficiaries', ['classification', 'status'], 'beneficiaries_classification_status_idx');
        $this->addIndex('beneficiaries', 'created_at', 'beneficiaries_created_at_idx');

        // Distribution event filters and date sorting.
        $this->addIndex('distribution_events', ['status', 'distribution_date'], 'distribution_events_status_date_idx');
        $this->addIndex('distribution_events', ['type', 'distribution_date'], 'distribution_events_type_date_idx');
        $this->addIndex('distribution_events', 'distribution_date', 'distribution_events_date_idx');

        // Allocation status/release queries inside event pages.
        $this->addIndex('allocations', ['distribution_event_id', 'release_outcome'], 'allocations_event_outcome_idx');
        $this->addIndex('allocations', ['distribution_event_id', 'distributed_at'], 'allocations_event_distributed_at_idx');
    }

    public function down(): void
    {
        $this->dropIndex('allocations', 'allocations_event_distributed_at_idx');
        $this->dropIndex('allocations', 'allocations_event_outcome_idx');

        $this->dropIndex('distribution_events', 'distribution_events_date_idx');
        $this->dropIndex('distribution_events', 'distribution_events_type_date_idx');
        $this->dropIndex('distribution_events', 'distribution_events_status_date_idx');

        $this->dropIndex('beneficiaries', 'beneficiaries_created_at_idx');
        $this->dropIndex('beneficiaries', 'beneficiaries_classification_status_idx');
        $this->dropIndex('beneficiaries', 'beneficiaries_status_agency_barangay_idx');
    }

    private function addIndex(string $tableName, array|string $columns, string $indexName): void
    {
        try {
            Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
                $table->index($columns, $indexName);
            });
        } catch (\Throwable) {
            // Ignore duplicate-index and DB-state mismatches.
        }
    }

    private function dropIndex(string $tableName, string $indexName): void
    {
        try {
            Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        } catch (\Throwable) {
            // Ignore if index does not exist in current DB state.
        }
    }
};
