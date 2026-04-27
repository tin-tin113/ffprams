<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Municipality: Enrique B. Magalona (E.B. Magalona), Negros Occidental
        // Coordinates sourced from OpenStreetMap / GeoNames (March 2026)
        // IMPORTANT: Geo-map module is scoped exclusively to E.B. Magalona
        $barangays = [
            ['name' => 'Alacaygan',     'latitude' => 10.84047410, 'longitude' => 123.05836290, 'municipality' => 'E.B. Magalona', 'province' => 'Negros Occidental', 'quadrant' => 'Quadrant 4'],
            ['name' => 'Alicante',      'latitude' => 10.89708870, 'longitude' => 123.01713470, 'municipality' => 'E.B. Magalona', 'province' => 'Negros Occidental', 'quadrant' => 'Quadrant 1'],
            ['name' => 'Batea',         'latitude' => 10.90692120, 'longitude' => 122.99754940, 'municipality' => 'E.B. Magalona', 'province' => 'Negros Occidental', 'quadrant' => 'Quadrant 1'],
            ['name' => 'Canlusong',     'latitude' => 10.75430870, 'longitude' => 123.16691120, 'municipality' => 'E.B. Magalona', 'province' => 'Negros Occidental', 'quadrant' => 'Quadrant 4'],
            ['name' => 'Consing',       'latitude' => 10.81383170, 'longitude' => 123.10607800, 'municipality' => 'E.B. Magalona', 'province' => 'Negros Occidental', 'quadrant' => 'Quadrant 4'],
            ['name' => 'Cudangdang',    'latitude' => 10.86583350, 'longitude' => 123.02199180, 'municipality' => 'E.B. Magalona', 'province' => 'Negros Occidental', 'quadrant' => 'Quadrant 4'],
            ['name' => 'Damgo',         'latitude' => 10.88405090, 'longitude' => 123.00456040, 'municipality' => 'E.B. Magalona', 'province' => 'Negros Occidental', 'quadrant' => 'Quadrant 1'],
            ['name' => 'Gahit',         'latitude' => 10.88890010, 'longitude' => 122.96999060, 'municipality' => 'E.B. Magalona', 'province' => 'Negros Occidental', 'quadrant' => 'Quadrant 2'],
            ['name' => 'Latasan',       'latitude' => 10.86122930, 'longitude' => 122.94838590, 'municipality' => 'E.B. Magalona', 'province' => 'Negros Occidental', 'quadrant' => 'Quadrant 3'],
            ['name' => 'Madalag',       'latitude' => 10.90040010, 'longitude' => 122.98298290, 'municipality' => 'E.B. Magalona', 'province' => 'Negros Occidental', 'quadrant' => 'Quadrant 1'],
            ['name' => 'Manta-angan',   'latitude' => 10.91372340, 'longitude' => 123.00602780, 'municipality' => 'E.B. Magalona', 'province' => 'Negros Occidental', 'quadrant' => 'Quadrant 1'],
            ['name' => 'Nanca',         'latitude' => 10.85446640, 'longitude' => 123.03422630, 'municipality' => 'E.B. Magalona', 'province' => 'Negros Occidental', 'quadrant' => 'Quadrant 4'],
            ['name' => 'Pasil',         'latitude' => 10.91645470, 'longitude' => 123.03851460, 'municipality' => 'E.B. Magalona', 'province' => 'Negros Occidental', 'quadrant' => 'Quadrant 1'],
            ['name' => 'Poblacion I',   'latitude' => 10.87850000, 'longitude' => 122.98080000, 'municipality' => 'E.B. Magalona', 'province' => 'Negros Occidental', 'quadrant' => 'Quadrant 1'],
            ['name' => 'Poblacion II',  'latitude' => 10.87780000, 'longitude' => 122.98180000, 'municipality' => 'E.B. Magalona', 'province' => 'Negros Occidental', 'quadrant' => 'Quadrant 4'],
            ['name' => 'Poblacion III', 'latitude' => 10.87802060, 'longitude' => 122.98126650, 'municipality' => 'E.B. Magalona', 'province' => 'Negros Occidental', 'quadrant' => 'Quadrant 4'],
            ['name' => 'San Isidro',    'latitude' => 10.79609900, 'longitude' => 123.13888550, 'municipality' => 'E.B. Magalona', 'province' => 'Negros Occidental', 'quadrant' => 'Quadrant 4'],
            ['name' => 'San Jose',      'latitude' => 10.87993900, 'longitude' => 123.00040400, 'municipality' => 'E.B. Magalona', 'province' => 'Negros Occidental', 'quadrant' => 'Quadrant 1'],
            ['name' => 'Santo Niño',    'latitude' => 10.86414320, 'longitude' => 122.98157720, 'municipality' => 'E.B. Magalona', 'province' => 'Negros Occidental', 'quadrant' => 'Quadrant 4'],
            ['name' => 'Tabigue',       'latitude' => 10.87799800, 'longitude' => 122.98857990, 'municipality' => 'E.B. Magalona', 'province' => 'Negros Occidental', 'quadrant' => 'Quadrant 4'],
            ['name' => 'Tanza',         'latitude' => 10.83532030, 'longitude' => 123.01137040, 'municipality' => 'E.B. Magalona', 'province' => 'Negros Occidental', 'quadrant' => 'Quadrant 4'],
            ['name' => 'Tomongtong',    'latitude' => 10.89197770, 'longitude' => 122.95366250, 'municipality' => 'E.B. Magalona', 'province' => 'Negros Occidental', 'quadrant' => 'Quadrant 2'],
            ['name' => 'Tuburan',       'latitude' => 10.87774800, 'longitude' => 122.95927640, 'municipality' => 'E.B. Magalona', 'province' => 'Negros Occidental', 'quadrant' => 'Quadrant 3'],
        ];

        $now = now();

        foreach ($barangays as $barangay) {
            DB::table('barangays')->updateOrInsert(
                ['name' => $barangay['name']],
                array_merge($barangay, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
