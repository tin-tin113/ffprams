<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$cols = \DB::select('SHOW COLUMNS FROM resource_types');
foreach ($cols as $col) {
    echo $col->Field . " (" . $col->Type . ")\n";
}
