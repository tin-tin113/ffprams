<?php

namespace App\Console\Commands;

use App\Models\Agency;
use App\Models\ProgramName;
use App\Models\ResourceType;
use App\Models\AssistancePurpose;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;

class GenerateSystemDocumentation extends Command
{
    protected $signature = 'doc:generate';

    protected $description = 'Generate FFPRAMS System Documentation PDF';

    public function handle()
    {
        $this->info('Generating FFPRAMS System Documentation PDF...');

        try {
            // Gather data
            $agencies = Agency::all();
            $programs = ProgramName::all();
            $resourceTypes = ResourceType::all();
            $purposes = AssistancePurpose::all();

            // Generate PDF
            $pdf = Pdf::loadView('docs.system-documentation', compact(
                'agencies',
                'programs',
                'resourceTypes',
                'purposes'
            ));

            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('margin-top', 0.5);
            $pdf->setOption('margin-right', 0.5);
            $pdf->setOption('margin-bottom', 0.5);
            $pdf->setOption('margin-left', 0.5);
            $pdf->setOption('defaultFont', 'Arial');
            $pdf->setOption('isRemoteEnabled', false);

            // Save to project root
            $path = base_path('FFPRAMS_System_Documentation.pdf');
            file_put_contents($path, $pdf->output());

            $this->info("Documentation PDF generated successfully: {$path}");
            return 0;
        } catch (\Exception $e) {
            $this->error("Error generating PDF: " . $e->getMessage());
            return 1;
        }
    }
}
