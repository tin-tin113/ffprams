<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$dbName = DB::getDatabaseName();
$tables = DB::select('SHOW TABLES');

$output = "# Database Tables and Columns\n\n";
$output .= "**Database:** $dbName\n\n";
$output .= "**Generated:** " . date('Y-m-d H:i:s') . "\n\n";

foreach ($tables as $table) {
    $tableName = current((array)$table);
    $output .= "## $tableName\n\n";
    $output .= "| Column | Type | Null | Key | Default | Extra |\n";
    $output .= "|---|---|---|---|---|---|\n";
    
    $columns = DB::select("SHOW COLUMNS FROM `$tableName` ");
    foreach ($columns as $col) {
        $extra = $col->Extra;
        if (strpos($col->Extra, 'STORED GENERATED') !== false) {
            $extra = 'STORED GENERATED';
        }
        $output .= "| {$col->Field} | {$col->Type} | {$col->Null} | {$col->Key} | {$col->Default} | {$extra} |\n";
    }
    $output .= "\n";
}

file_put_contents('DATABASE_TABLES_AND_COLUMNS.md', $output);
echo "File generated successfully.\n";
