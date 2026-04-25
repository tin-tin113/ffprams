<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$dbName = DB::getDatabaseName();

// 1. Get all tables and columns
$tables = DB::select("SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY, COLUMN_DEFAULT, EXTRA 
                      FROM information_schema.columns 
                      WHERE TABLE_SCHEMA = '$dbName' 
                      ORDER BY TABLE_NAME, ORDINAL_POSITION");

// 2. Get all foreign keys
$foreignKeys = DB::select("SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
                           FROM information_schema.key_column_usage 
                           WHERE TABLE_SCHEMA = '$dbName' 
                           AND REFERENCED_TABLE_NAME IS NOT NULL");

$schema = [];
foreach ($tables as $col) {
    $schema[$col->TABLE_NAME]['columns'][] = [
        'name' => $col->COLUMN_NAME,
        'type' => $col->COLUMN_TYPE,
        'null' => $col->IS_NULLABLE,
        'key' => $col->COLUMN_KEY,
        'default' => $col->COLUMN_DEFAULT,
        'extra' => $col->EXTRA
    ];
}

$connections = [];
foreach ($foreignKeys as $fk) {
    $connections[] = [
        'from_table' => $fk->TABLE_NAME,
        'from_column' => $fk->COLUMN_NAME,
        'to_table' => $fk->REFERENCED_TABLE_NAME,
        'to_column' => $fk->REFERENCED_COLUMN_NAME
    ];
}

$output = "# 🛡️ Verified Database Schema & Foreign Key Connections\n\n";
$output .= "**Database:** $dbName\n";
$output .= "**Last Verified:** " . date('Y-m-d H:i:s') . "\n\n";

$output .= "## 🔗 Actual Database Connections (Foreign Keys)\n";
$output .= "| Source Table | Source Column | Target Table | Target Column |\n";
$output .= "|---|---|---|---|\n";
foreach ($connections as $conn) {
    $output .= "| `{$conn['from_table']}` | `{$conn['from_column']}` | `{$conn['to_table']}` | `{$conn['to_column']}` |\n";
}
$output .= "\n---\n\n";

$output .= "## 📑 Comprehensive Table Definitions (Zero Omissions)\n";
foreach ($schema as $tableName => $data) {
    $output .= "### $tableName\n";
    $output .= "| Column | Type | Null | Key | Default | Extra |\n";
    $output .= "|---|---|---|---|---|---|\n";
    foreach ($data['columns'] as $col) {
        $output .= "| {$col['name']} | {$col['type']} | {$col['null']} | {$col['key']} | {$col['default']} | {$col['extra']} |\n";
    }
    $output .= "\n";
}

file_put_contents('DATABASE_VERIFIED_SCHEMA.md', $output);
echo "Exhaustive schema and connections verified and written to DATABASE_VERIFIED_SCHEMA.md\n";
