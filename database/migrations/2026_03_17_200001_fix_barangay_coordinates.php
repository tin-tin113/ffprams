<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Update barangay coordinates with accurate GPS data from OpenStreetMap.
     */
    public function up(): void
    {
        $coordinates = [
            'Alacaygan'     => ['latitude' => 10.84047410, 'longitude' => 123.05836290],
            'Alicante'      => ['latitude' => 10.89708870, 'longitude' => 123.01713470],
            'Batea'         => ['latitude' => 10.90692120, 'longitude' => 122.99754940],
            'Canlusong'     => ['latitude' => 10.75430870, 'longitude' => 123.16691120],
            'Consing'       => ['latitude' => 10.81383170, 'longitude' => 123.10607800],
            'Cudangdang'    => ['latitude' => 10.86583350, 'longitude' => 123.02199180],
            'Damgo'         => ['latitude' => 10.88405090, 'longitude' => 123.00456040],
            'Gahit'         => ['latitude' => 10.88890010, 'longitude' => 122.96999060],
            'Latasan'       => ['latitude' => 10.86122930, 'longitude' => 122.94838590],
            'Madalag'       => ['latitude' => 10.90040010, 'longitude' => 122.98298290],
            'Manta-angan'   => ['latitude' => 10.91372340, 'longitude' => 123.00602780],
            'Nanca'         => ['latitude' => 10.85446640, 'longitude' => 123.03422630],
            'Pasil'         => ['latitude' => 10.91645470, 'longitude' => 123.03851460],
            'Poblacion I'   => ['latitude' => 10.87850000, 'longitude' => 122.98080000],
            'Poblacion II'  => ['latitude' => 10.87780000, 'longitude' => 122.98180000],
            'Poblacion III' => ['latitude' => 10.87802060, 'longitude' => 122.98126650],
            'San Isidro'    => ['latitude' => 10.79609900, 'longitude' => 123.13888550],
            'San Jose'      => ['latitude' => 10.87993900, 'longitude' => 123.00040400],
            'Santo Niño'    => ['latitude' => 10.86414320, 'longitude' => 122.98157720],
            'Tabigue'       => ['latitude' => 10.87799800, 'longitude' => 122.98857990],
            'Tanza'         => ['latitude' => 10.83532030, 'longitude' => 123.01137040],
            'Tomongtong'    => ['latitude' => 10.89197770, 'longitude' => 122.95366250],
            'Tuburan'       => ['latitude' => 10.87774800, 'longitude' => 122.95927640],
        ];

        foreach ($coordinates as $name => $coords) {
            DB::table('barangays')
                ->where('name', $name)
                ->update($coords);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $oldCoordinates = [
            'Alacaygan'     => ['latitude' => 10.8820, 'longitude' => 122.9780],
            'Alicante'      => ['latitude' => 10.8850, 'longitude' => 122.9850],
            'Batea'         => ['latitude' => 10.8700, 'longitude' => 122.9750],
            'Canlusong'     => ['latitude' => 10.8730, 'longitude' => 122.9880],
            'Consing'       => ['latitude' => 10.8690, 'longitude' => 122.9830],
            'Cudangdang'    => ['latitude' => 10.8760, 'longitude' => 122.9900],
            'Damgo'         => ['latitude' => 10.8800, 'longitude' => 122.9860],
            'Gahit'         => ['latitude' => 10.8840, 'longitude' => 122.9740],
            'Latasan'       => ['latitude' => 10.8710, 'longitude' => 122.9790],
            'Madalag'       => ['latitude' => 10.8680, 'longitude' => 122.9770],
            'Manta-angan'   => ['latitude' => 10.8790, 'longitude' => 122.9720],
            'Nanca'         => ['latitude' => 10.8750, 'longitude' => 122.9840],
            'Pasil'         => ['latitude' => 10.8830, 'longitude' => 122.9810],
            'Poblacion I'   => ['latitude' => 10.8770, 'longitude' => 122.9814],
            'Poblacion II'  => ['latitude' => 10.8775, 'longitude' => 122.9820],
            'Poblacion III' => ['latitude' => 10.8765, 'longitude' => 122.9808],
            'San Isidro'    => ['latitude' => 10.8720, 'longitude' => 122.9870],
            'San Jose'      => ['latitude' => 10.8860, 'longitude' => 122.9760],
            'Santo Niño'    => ['latitude' => 10.8740, 'longitude' => 122.9890],
            'Tabigue'       => ['latitude' => 10.8810, 'longitude' => 122.9700],
            'Tanza'         => ['latitude' => 10.8670, 'longitude' => 122.9850],
            'Tomongtong'    => ['latitude' => 10.8780, 'longitude' => 122.9730],
            'Tuburan'       => ['latitude' => 10.8870, 'longitude' => 122.9800],
        ];

        foreach ($oldCoordinates as $name => $coords) {
            DB::table('barangays')
                ->where('name', $name)
                ->update($coords);
        }
    }
};
