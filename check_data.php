<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DirectAssistance;
use App\Models\Allocation;

$daAll = DirectAssistance::all();
$orphans = [];
foreach ($daAll as $item) {
    $exists = Allocation::where('beneficiary_id', $item->beneficiary_id)
        ->where('distribution_event_id', $item->distribution_event_id)
        ->where('resource_type_id', $item->resource_type_id)
        ->exists();
    if (!$exists) {
        $orphans[] = $item;
    }
}

echo "Direct Assistance Total: " . $daAll->count() . "\n";
echo "Direct Assistance NOT in Allocations: " . count($orphans) . "\n";

$directAllocations = Allocation::where('release_method', 'direct')->get();
echo "Direct Allocations in allocations table: " . $directAllocations->count() . "\n";

$daWithEvent = Allocation::where('release_method', 'direct')->whereNotNull('distribution_event_id')->count();
$daWithoutEvent = Allocation::where('release_method', 'direct')->whereNull('distribution_event_id')->count();
echo "Direct Allocations with Event ID: $daWithEvent\n";
echo "Direct Allocations without Event ID: $daWithoutEvent\n";
