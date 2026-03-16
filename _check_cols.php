<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo 'Total form field options: ' . App\Models\FormFieldOption::count() . PHP_EOL;
echo 'Field groups: ' . implode(', ', App\Models\FormFieldOption::distinct()->pluck('field_group')->toArray()) . PHP_EOL;
echo 'id_type options: ' . App\Models\FormFieldOption::where('field_group', 'id_type')->count() . PHP_EOL;
