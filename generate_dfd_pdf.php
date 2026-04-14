<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Barryvdh\DomPDF\Facade\Pdf;

try {
    $html = file_get_contents(storage_path('app/dfd_hierarchy.html'));
    $pdf = Pdf::loadHTML($html);
    $pdf->setPaper('A4', 'portrait');
    $pdf->save(public_path('FFPRAMS_DFD_Hierarchy.pdf'));
    echo "PDF generated successfully at public/FFPRAMS_DFD_Hierarchy.pdf\n";
} catch (\Exception $e) {
    echo "Error generating PDF: " . $e->getMessage() . "\n";
}
