<?php

use App\Models\Allocation;
use App\Models\DirectAssistance;
use App\Models\RecordAttachment;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function validate() {
    echo "--- VALIDATION START ---\n";

    // 1. Count check
    $daCount = DB::table('direct_assistance')->count();
    $daDeletedCount = DB::table('direct_assistance')->whereNotNull('deleted_at')->count();
    
    $migratedCount = Allocation::where('release_method', 'direct')->withTrashed()->count();
    $migratedDeletedCount = Allocation::where('release_method', 'direct')->onlyTrashed()->count();

    echo "Direct Assistance Total: $daCount (Deleted: $daDeletedCount)\n";
    echo "Allocations (Direct) Total: $migratedCount (Deleted: $migratedDeletedCount)\n";

    if ($daCount !== $migratedCount) {
        echo "FAIL: Count mismatch!\n";
    } else {
        echo "PASS: Count matches ($daCount)\n";
    }

    if ($daDeletedCount !== $migratedDeletedCount) {
        echo "FAIL: Deleted count mismatch!\n";
    } else {
        echo "PASS: Deleted count matches ($daDeletedCount)\n";
    }

    // 2. Data Integrity Check (Sample or All)
    echo "\n--- RECORD DATA COMPARISON ---\n";
    $daRecords = DB::table('direct_assistance')->get();
    $mismatches = 0;

    foreach ($daRecords as $da) {
        $allocation = Allocation::withTrashed()
            ->where('legacy_id', $da->id)
            ->where('legacy_source', 'direct_assistance')
            ->first();

        if (!$allocation) {
            echo "FAIL: Record ID {$da->id} not found in Allocations!\n";
            $mismatches++;
            continue;
        }

        // Basic field checks
        $checks = [
            'beneficiary_id' => $da->beneficiary_id,
            'program_name_id' => $da->program_name_id,
            'resource_type_id' => $da->resource_type_id,
            'quantity' => $da->quantity,
            'amount' => $da->amount,
            'remarks' => $da->remarks,
        ];

        foreach ($checks as $field => $val) {
            if ((string)$allocation->$field !== (string)$val) {
                echo "FAIL: ID {$da->id} field $field mismatch: DA({$val}) vs Allocation({$allocation->$field})\n";
                $mismatches++;
            }
        }
        
        // Status mapping check (simplified)
        if ($da->status === 'released' && !$allocation->distributed_at) {
             echo "FAIL: ID {$da->id} status 'released' but Allocation distributed_at is NULL\n";
             $mismatches++;
        }
    }

    if ($mismatches === 0) {
        echo "PASS: All records field-checked successfully.\n";
    } else {
        echo "FAIL: $mismatches mismatches found.\n";
    }

    // 3. Attachment Check
    echo "\n--- ATTACHMENT POLYMORPHIC TYPE CHECK ---\n";
    $remainingLegacyAttachments = RecordAttachment::where('attachable_type', 'App\Models\DirectAssistance')->count();
    $newAttachments = RecordAttachment::where('attachable_type', 'App\Models\Allocation')
        ->whereIn('attachable_id', Allocation::where('release_method', 'direct')->pluck('id'))
        ->count();

    echo "Legacy attachable_type records remaining: $remainingLegacyAttachments\n";
    echo "Attachments correctly linked to new Allocations: $newAttachments\n";

    if ($remainingLegacyAttachments > 0) {
        echo "FAIL: Still found attachments pointing to DirectAssistance!\n";
    } else {
        echo "PASS: No attachments pointing to legacy model.\n";
    }

    echo "--- VALIDATION END ---\n";
}

validate();
