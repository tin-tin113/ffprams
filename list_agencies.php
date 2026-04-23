<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Agency;

$agencies = Agency::all();
foreach ($agencies as $agency) {
    echo "ID: {$agency->id} | Name: {$agency->name} | Full Name: {$agency->full_name}\n";
}
