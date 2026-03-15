<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuditLogService
{
    public function log(
        int $userId,
        string $action,
        string $tableName,
        int $recordId,
        array $oldValues = [],
        array $newValues = [],
    ): void {
        try {
            DB::table('audit_logs')->insert([
                'user_id'    => $userId,
                'action'     => $action,
                'table_name' => $tableName,
                'record_id'  => $recordId,
                'old_values' => empty($oldValues) ? null : json_encode($oldValues),
                'new_values' => empty($newValues) ? null : json_encode($newValues),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('AuditLogService: Failed to write audit log', [
                'action'     => $action,
                'table_name' => $tableName,
                'record_id'  => $recordId,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
