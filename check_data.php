<?php
// Quick verification script - run this in Tinker
// php artisan tinker < check_data.php

use App\Models\Agency;
use App\Models\Classification;
use App\Models\AgencyFormField;

echo "=== CLASSIFICATIONS ===\n";
$classifications = Classification::all();
foreach ($classifications as $c) {
    echo "ID: {$c->id}, Name: {$c->name}\n";
}

echo "\n=== AGENCIES ===\n";
$agencies = Agency::with('classifications')->get();
foreach ($agencies as $a) {
    $classNames = $a->classifications->pluck('name')->join(', ') ?: 'NONE';
    echo "ID: {$a->id}, Name: {$a->name}, Classifications: {$classNames}\n";
}

echo "\n=== AGENCY FORM FIELDS ===\n";
$fields = AgencyFormField::with('agency')->get();
foreach ($fields as $f) {
    echo "ID: {$f->id}, Agency: {$f->agency->name}, Field: {$f->display_label} ({$f->field_type}, Required: " . ($f->is_required ? 'Yes' : 'No') . ")\n";
}

echo "\n=== COUNT SUMMARY ===\n";
echo "Classifications: " . Classification::count() . "\n";
echo "Agencies: " . Agency::count() . "\n";
echo "Agency Form Fields: " . AgencyFormField::count() . "\n";
echo "Agency Classifications (Pivot): " . \DB::table('agency_classifications')->count() . "\n";

exit();
?>
