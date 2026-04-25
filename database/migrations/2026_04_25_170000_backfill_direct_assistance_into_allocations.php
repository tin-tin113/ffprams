<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Backfill direct_assistance rows into allocations in an idempotent way.
     */
    public function up(): void
    {
        if (! Schema::hasTable('direct_assistance') || ! Schema::hasTable('allocations')) {
            return;
        }

        $requiredAllocationColumns = [
            'beneficiary_id',
            'program_name_id',
            'resource_type_id',
            'release_method',
            'legacy_id',
            'legacy_source',
        ];

        foreach ($requiredAllocationColumns as $column) {
            if (! Schema::hasColumn('allocations', $column)) {
                return;
            }
        }

        $requiredDirectColumns = [
            'id',
            'beneficiary_id',
            'program_name_id',
            'resource_type_id',
            'created_at',
            'updated_at',
        ];

        foreach ($requiredDirectColumns as $column) {
            if (! Schema::hasColumn('direct_assistance', $column)) {
                return;
            }
        }

        if (! Schema::hasColumn('direct_assistance', 'id')) {
            return;
        }

        $hasDistributionEventId = Schema::hasColumn('allocations', 'distribution_event_id')
            && Schema::hasColumn('direct_assistance', 'distribution_event_id');
        $hasAssistancePurposeId = Schema::hasColumn('allocations', 'assistance_purpose_id')
            && Schema::hasColumn('direct_assistance', 'assistance_purpose_id');
        $hasQuantity = Schema::hasColumn('allocations', 'quantity')
            && Schema::hasColumn('direct_assistance', 'quantity');
        $hasAmount = Schema::hasColumn('allocations', 'amount')
            && Schema::hasColumn('direct_assistance', 'amount');
        $hasIsReadyForRelease = Schema::hasColumn('allocations', 'is_ready_for_release')
            && Schema::hasColumn('direct_assistance', 'status');
        $hasReleaseOutcome = Schema::hasColumn('allocations', 'release_outcome')
            && (Schema::hasColumn('direct_assistance', 'release_outcome') || Schema::hasColumn('direct_assistance', 'status'));
        $hasRemarks = Schema::hasColumn('allocations', 'remarks') && Schema::hasColumn('direct_assistance', 'remarks');
        $hasCreatedBy = Schema::hasColumn('allocations', 'created_by') && Schema::hasColumn('direct_assistance', 'created_by');
        $hasDistributedBy = Schema::hasColumn('allocations', 'distributed_by') && Schema::hasColumn('direct_assistance', 'distributed_by');
        $hasDistributedAt = Schema::hasColumn('allocations', 'distributed_at') && Schema::hasColumn('direct_assistance', 'distributed_at');
        $hasDeletedAt = Schema::hasColumn('direct_assistance', 'deleted_at');

        $selectColumns = [
            'id',
            'beneficiary_id',
            'program_name_id',
            'resource_type_id',
            'created_at',
            'updated_at',
        ];

        foreach ([
            'assistance_purpose_id',
            'quantity',
            'amount',
            'status',
            'release_outcome',
            'remarks',
            'created_by',
            'distributed_by',
            'distributed_at',
            'distribution_event_id',
        ] as $optionalColumn) {
            if (Schema::hasColumn('direct_assistance', $optionalColumn)) {
                $selectColumns[] = $optionalColumn;
            }
        }

        $directQuery = DB::table('direct_assistance')->select($selectColumns);

        if ($hasDeletedAt) {
            $directQuery->whereNull('deleted_at');
        }

        $directQuery
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (
                $hasDistributionEventId,
                $hasAssistancePurposeId,
                $hasQuantity,
                $hasAmount,
                $hasIsReadyForRelease,
                $hasReleaseOutcome,
                $hasRemarks,
                $hasCreatedBy,
                $hasDistributedBy,
                $hasDistributedAt
            ): void {
                $ids = collect($rows)->pluck('id')->filter()->values();
                if ($ids->isEmpty()) {
                    return;
                }

                $alreadyMigrated = DB::table('allocations')
                    ->where('legacy_source', 'direct_assistance')
                    ->whereIn('legacy_id', $ids)
                    ->pluck('legacy_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();

                $alreadyMigratedMap = array_flip($alreadyMigrated);

                $payload = [];
                foreach ($rows as $row) {
                    $legacyId = (int) $row->id;
                    if (isset($alreadyMigratedMap[$legacyId])) {
                        continue;
                    }

                    $insert = [
                        'beneficiary_id' => $row->beneficiary_id,
                        'program_name_id' => $row->program_name_id,
                        'resource_type_id' => $row->resource_type_id,
                        'release_method' => 'direct',
                        'legacy_id' => $legacyId,
                        'legacy_source' => 'direct_assistance',
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ];

                    if ($hasDistributionEventId) {
                        $insert['distribution_event_id'] = $row->distribution_event_id;
                    }

                    if ($hasAssistancePurposeId) {
                        $insert['assistance_purpose_id'] = $row->assistance_purpose_id;
                    }

                    if ($hasQuantity) {
                        $insert['quantity'] = $row->quantity;
                    }

                    if ($hasAmount) {
                        $insert['amount'] = $row->amount;
                    }

                    if ($hasIsReadyForRelease) {
                        $insert['is_ready_for_release'] = $row->status === 'ready_for_release';
                    }

                    if ($hasReleaseOutcome) {
                        $status = strtolower((string) ($row->status ?? ''));
                        $legacyOutcome = strtolower((string) ($row->release_outcome ?? ''));

                        if (in_array($status, ['released', 'completed', 'distributed'], true)) {
                            $insert['release_outcome'] = 'received';
                        } elseif (in_array($status, ['not_received'], true)) {
                            $insert['release_outcome'] = 'not_received';
                        } elseif (in_array($legacyOutcome, ['refused', 'not_found'], true)) {
                            $insert['release_outcome'] = 'not_received';
                        } elseif (in_array($legacyOutcome, ['accepted', 'partially_received'], true)) {
                            $insert['release_outcome'] = 'received';
                        } else {
                            $insert['release_outcome'] = null;
                        }
                    }

                    if ($hasRemarks) {
                        $insert['remarks'] = $row->remarks;
                    }

                    if ($hasCreatedBy) {
                        $insert['created_by'] = $row->created_by;
                    }

                    if ($hasDistributedBy) {
                        $insert['distributed_by'] = $row->distributed_by;
                    }

                    if ($hasDistributedAt) {
                        $insert['distributed_at'] = $row->distributed_at;
                    }

                    $payload[] = $insert;
                }

                if (! empty($payload)) {
                    DB::table('allocations')->insert($payload);
                }
            }, 'id');
    }

    /**
     * Reverse backfill by removing only rows inserted by this migration marker.
     */
    public function down(): void
    {
        if (! Schema::hasTable('allocations') || ! Schema::hasColumn('allocations', 'legacy_source')) {
            return;
        }

        DB::table('allocations')
            ->where('legacy_source', 'direct_assistance')
            ->delete();
    }
};
