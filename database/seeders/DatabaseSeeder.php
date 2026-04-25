<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            BarangaySeeder::class,
            AgencySeeder::class,
            ProgramNameSeeder::class,
            ResourceTypeSeeder::class,
            AssistancePurposeSeeder::class,
            FormFieldOptionSeeder::class,
            BeneficiaryPerBarangaySeeder::class,
            DistributionEventSeeder::class,
            EventAllocationSeeder::class,
            DirectAllocationSeeder::class,
        ]);
    }
}
