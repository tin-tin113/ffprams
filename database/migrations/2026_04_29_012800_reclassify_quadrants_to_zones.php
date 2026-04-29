<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Reclassify barangay quadrants from 4 directional quadrants
     * (Quadrant 1–4) to 3 socio-geographic zones:
     *   - Urban Center
     *   - Coastal Area
     *   - Upland & Inland
     */
    public function up(): void
    {
        // Urban Center: Poblacion areas + adjacent high-density barangays
        DB::table('barangays')
            ->whereIn('name', [
                'Poblacion I', 'Poblacion II', 'Poblacion III',
                'Damgo', 'San Jose', 'Santo Niño', 'Tabigue',
            ])
            ->update(['quadrant' => 'Urban Center']);

        // Coastal Area: Shoreline + northern barangays
        DB::table('barangays')
            ->whereIn('name', [
                'Alicante', 'Batea', 'Gahit', 'Latasan',
                'Madalag', 'Manta-angan', 'Pasil',
                'Tomongtong', 'Tuburan',
            ])
            ->update(['quadrant' => 'Coastal Area']);

        // Upland & Inland: Interior / agricultural barangays
        DB::table('barangays')
            ->whereIn('name', [
                'Alacaygan', 'Canlusong', 'Consing', 'Cudangdang',
                'Nanca', 'San Isidro', 'Tanza',
            ])
            ->update(['quadrant' => 'Upland & Inland']);
    }

    /**
     * Revert to the original 4-quadrant system.
     */
    public function down(): void
    {
        $mapping = [
            'Quadrant 1' => ['Alicante', 'Batea', 'Damgo', 'Madalag', 'Manta-angan', 'Pasil', 'Poblacion I', 'San Jose'],
            'Quadrant 2' => ['Gahit', 'Tomongtong'],
            'Quadrant 3' => ['Latasan', 'Tuburan'],
            'Quadrant 4' => ['Alacaygan', 'Canlusong', 'Consing', 'Cudangdang', 'Nanca', 'Poblacion II', 'Poblacion III', 'San Isidro', 'Santo Niño', 'Tabigue', 'Tanza'],
        ];

        foreach ($mapping as $quadrant => $barangays) {
            DB::table('barangays')
                ->whereIn('name', $barangays)
                ->update(['quadrant' => $quadrant]);
        }
    }
};
