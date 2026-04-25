<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = DB::select('SHOW TABLES');
$result = [];
foreach ($tables as $table) {
    $tableName = current((array)$table);
    $columns = DB::select("SHOW COLUMNS FROM `$tableName` text");
    $result[$tableName] = $columns;
}
echo json_encode($result, JSON_PRETTY_PRINT);
