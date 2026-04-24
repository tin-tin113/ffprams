<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$agency = \App\Models\Agency::where('name', 'DAR')->first();
if ($agency) {
    echo "Agency: " . $agency->name . "\n";
    foreach ($agency->formFields as $f) {
        echo "- " . $f->field_name . " (Section: " . $f->form_section . ")\n";
    }
} else {
    echo "DAR Agency not found\n";
}
