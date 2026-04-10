<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReleaseOutcomeService
{
    public function apply(
        Model $record,
        array $updates,
        AuditLogService $audit,
        string $action,
        string $tableName,
    ): void {
        DB::transaction(function () use ($record, $updates, $audit, $action, $tableName) {
            $oldValues = $record->toArray();
            $record->update($updates);

            $audit->log(
                (int) (request()->user()?->id ?? 0),
                $action,
                $tableName,
                (int) $record->getKey(),
                $oldValues,
                $record->fresh()->toArray(),
            );
        });
    }
}
