<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

try {
    $markdown = file_get_contents(__DIR__ . '/dfd_hierarchy.md');
    $htmlBody = Str::markdown($markdown);
    
    $html = <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; line-height: 1.4; color: #333; }
            h1 { color: #2c3e50; font-size: 16px; border-bottom: 2px solid #34495e; padding-bottom: 4px; margin-bottom: 12px; page-break-after: avoid;}
            h2 { color: #2980b9; font-size: 14px; margin-top: 15px; border-bottom: 1px solid #bdc3c7; padding-bottom: 2px; page-break-after: avoid;}
            h3 { background-color: #ecf0f1; padding: 4px 6px; font-size: 12px; margin-top: 10px; border-left: 3px solid #3498db; page-break-after: avoid;}
            ul { margin-top: 4px; margin-bottom: 8px; padding-left: 20px;}
            li { margin-bottom: 4px; }
            strong { color: #2c3e50; }
        </style>
    </head>
    <body>
        {$htmlBody}
    </body>
    </html>
HTML;

    $pdf = Pdf::loadHTML($html);
    $pdf->setPaper('A4', 'portrait');
    $pdf->save(public_path('FFPRAMS_DFD_Hierarchy.pdf'));
    echo "PDF generated successfully at public/FFPRAMS_DFD_Hierarchy.pdf";    
} catch (\Exception $e) {
    echo "Error generating PDF: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
