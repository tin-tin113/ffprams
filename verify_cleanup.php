<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Agency;
use App\Models\Barangay;

echo "\n=== AGENCY VERIFICATION ===\n\n";
echo "Active Agencies:\n";
foreach (Agency::where('is_active', true)->get() as $a) {
    echo "  - {$a->name}: {$a->full_name}\n";
}

echo "\nDeactivated Agencies:\n";
foreach (Agency::where('is_active', false)->get() as $a) {
    echo "  - {$a->name}: {$a->full_name}\n";
}

echo "\n=== BARANGAY VERIFICATION ===\n\n";
$barangays = Barangay::orderBy('name')->get();
echo "Total Barangays: " . $barangays->count() . "\n\n";
echo "List of Barangays:\n";
$i = 1;
foreach ($barangays as $b) {
    echo sprintf("  %2d. %s\n", $i++, $b->name);
}
echo "\n";
