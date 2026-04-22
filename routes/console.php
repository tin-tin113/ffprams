<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;
use App\Models\ProgramName;

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

Artisan::command('programs:sync-classifications {--dry-run : Show mismatches without updating records}', function (): void {
    $dryRun = (bool) $this->option('dry-run');

    $programs = ProgramName::query()
        ->with('agency.classifications:id,name')
        ->orderBy('id')
        ->get();

    $checked = 0;
    $updated = 0;
    $skipped = 0;
    $wouldUpdate = 0;

    foreach ($programs as $program) {
        $checked++;

        $classificationNames = $program->agency?->classifications
            ->pluck('name')
            ->map(fn ($name) => strtolower(trim((string) $name)))
            ->filter()
            ->unique()
            ->values()
            ?? collect();

        $derivedClassification = match (true) {
            $classificationNames->contains('farmer') && $classificationNames->contains('fisherfolk') => 'Both',
            $classificationNames->contains('farmer') => 'Farmer',
            $classificationNames->contains('fisherfolk') => 'Fisherfolk',
            default => null,
        };

        if ($derivedClassification === null) {
            $skipped++;
            $this->warn("Skipped program {$program->id} ({$program->name}) - agency has no valid classification mapping.");
            continue;
        }

        if ($program->classification === $derivedClassification) {
            continue;
        }

        if ($dryRun) {
            $this->line("Would update program {$program->id} ({$program->name}) from {$program->classification} to {$derivedClassification}.");
            $wouldUpdate++;
            continue;
        }

        $oldClassification = $program->classification;
        $program->update(['classification' => $derivedClassification]);
        $updated++;

        $this->info("Updated program {$program->id} ({$program->name}) from {$oldClassification} to {$derivedClassification}.");
    }

    if ($dryRun) {
        $this->info("Dry run complete. Checked {$checked} program(s); {$skipped} skipped; {$wouldUpdate} would update.");
        return;
    }

    $this->info("Sync complete. Checked {$checked} program(s); updated {$updated}; skipped {$skipped}.");
})->purpose('Sync existing program classifications to the selected agency classification mapping');

Schedule::command('reports:prune-allocation-import-errors')->dailyAt('01:15');
