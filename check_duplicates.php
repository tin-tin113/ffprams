<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Agency;
use Illuminate\Support\Facades\DB;

$duplicates = Agency::select('name', DB::raw('COUNT(*) as count'))
    ->groupBy('name')
    ->having('count', '>', 1)
    ->get();

if ($duplicates->isEmpty()) {
    echo "No duplicate agency names found.\n";
} else {
    echo "Found duplicate agency names:\n";
    foreach ($duplicates as $duplicate) {
        echo "- {$duplicate->name}: {$duplicate->count} entries\n";
    }
}
