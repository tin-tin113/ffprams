<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PruneAllocationImportReportsCommandTest extends TestCase
{
    public function test_prune_command_deletes_only_old_matching_report_files(): void
    {
        Storage::fake('allocation_import_reports');

        $disk = Storage::disk('allocation_import_reports');

        $oldReport = 'allocation-import-errors-event-7-20260401-120000-11111111-1111-4111-8111-111111111111.csv';
        $recentReport = 'allocation-import-errors-event-7-20260402-120000-22222222-2222-4222-8222-222222222222.csv';
        $unrelatedFile = 'keep-this-file.csv';

        $disk->put($oldReport, "line,error\n2,Old row\n");
        $disk->put($recentReport, "line,error\n3,Recent row\n");
        $disk->put($unrelatedFile, "line,error\n4,Other file\n");

        touch($disk->path($oldReport), now()->subDays(10)->getTimestamp());
        touch($disk->path($recentReport), now()->subDays(2)->getTimestamp());
        touch($disk->path($unrelatedFile), now()->subDays(20)->getTimestamp());

        $this->artisan('reports:prune-allocation-import-errors', ['--days' => 7])
            ->assertExitCode(0);

        $this->assertFalse($disk->exists($oldReport));
        $this->assertTrue($disk->exists($recentReport));
        $this->assertTrue($disk->exists($unrelatedFile));
    }
}
