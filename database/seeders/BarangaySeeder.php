<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangaySeeder extends Seeder
{
    public function run(): void
    {
        // Municipality center: 10.8770° N, 122.9814° E
        // Individual coordinates are approximate offsets from the center.
        // TODO: Replace with surveyed GPS coordinates per barangay.
        $barangays = [
            ['name' => 'Alacaygan',     'latitude' => 10.8820, 'longitude' => 122.9780],
            ['name' => 'Alicante',      'latitude' => 10.8850, 'longitude' => 122.9850],
            ['name' => 'Batea',         'latitude' => 10.8700, 'longitude' => 122.9750],
            ['name' => 'Canlusong',     'latitude' => 10.8730, 'longitude' => 122.9880],
            ['name' => 'Consing',       'latitude' => 10.8690, 'longitude' => 122.9830],
            ['name' => 'Cudangdang',    'latitude' => 10.8760, 'longitude' => 122.9900],
            ['name' => 'Damgo',         'latitude' => 10.8800, 'longitude' => 122.9860],
            ['name' => 'Gahit',         'latitude' => 10.8840, 'longitude' => 122.9740],
            ['name' => 'Latasan',       'latitude' => 10.8710, 'longitude' => 122.9790],
            ['name' => 'Madalag',       'latitude' => 10.8680, 'longitude' => 122.9770],
            ['name' => 'Manta-angan',   'latitude' => 10.8790, 'longitude' => 122.9720],
            ['name' => 'Nanca',         'latitude' => 10.8750, 'longitude' => 122.9840],
            ['name' => 'Pasil',         'latitude' => 10.8830, 'longitude' => 122.9810],
            ['name' => 'Poblacion I',   'latitude' => 10.8770, 'longitude' => 122.9814],
            ['name' => 'Poblacion II',  'latitude' => 10.8775, 'longitude' => 122.9820],
            ['name' => 'Poblacion III', 'latitude' => 10.8765, 'longitude' => 122.9808],
            ['name' => 'San Isidro',    'latitude' => 10.8720, 'longitude' => 122.9870],
            ['name' => 'San Jose',      'latitude' => 10.8860, 'longitude' => 122.9760],
            ['name' => 'Santo Niño',    'latitude' => 10.8740, 'longitude' => 122.9890],
            ['name' => 'Tabigue',       'latitude' => 10.8810, 'longitude' => 122.9700],
            ['name' => 'Tanza',         'latitude' => 10.8670, 'longitude' => 122.9850],
            ['name' => 'Tomongtong',    'latitude' => 10.8780, 'longitude' => 122.9730],
            ['name' => 'Tuburan',       'latitude' => 10.8870, 'longitude' => 122.9800],
        ];

        $now = now();

        foreach ($barangays as &$barangay) {
            $barangay['created_at'] = $now;
            $barangay['updated_at'] = $now;
        }

        DB::table('barangays')->insert($barangays);
    }
}
