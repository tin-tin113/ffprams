<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('reports:prune-allocation-import-errors {--days= : Number of days to retain generated CSV error reports}', function (): void {
    $daysOption = $this->option('days');
    $retentionDays = is_numeric($daysOption)
        ? (int) $daysOption
        : (int) config('filesystems.allocation_import_reports_retention_days', 14);

    if ($retentionDays < 1) {
        $retentionDays = 1;
    }

    $cutoffTimestamp = now()->subDays($retentionDays)->getTimestamp();
    $disk = Storage::disk('allocation_import_reports');

    $examined = 0;
    $deleted = 0;

    foreach ($disk->allFiles() as $file) {
        $filename = basename($file);

        if (! preg_match('/^allocation-import-errors-event-\d+-\d{8}-\d{6}-[a-f0-9-]+\.csv$/i', $filename)) {
            continue;
        }

        $examined++;

        try {
            $lastModified = $disk->lastModified($file);
        } catch (\Throwable) {
            continue;
        }

        if ($lastModified <= $cutoffTimestamp && $disk->delete($file)) {
            $deleted++;
        }
    }

    $this->info("Pruned {$deleted} allocation import error report(s); examined {$examined} file(s). Retention: {$retentionDays} day(s).");
})->purpose('Prune old allocation CSV import error report files');

Schedule::command('reports:prune-allocation-import-errors')->dailyAt('01:15');
