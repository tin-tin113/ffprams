<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Agency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::updateOrCreate(
            ['email' => 'admin@ffprams.com'],
            [
                'name'     => 'System Admin',
                'password' => Hash::make('Admin@1234'),
                'role'     => 'admin',
            ]
        );

        // Staff
        User::updateOrCreate(
            ['email' => 'staff@ffprams.com'],
            [
                'name'     => 'LGU Staff',
                'password' => Hash::make('Staff@1234'),
                'role'     => 'staff',
            ]
        );

        // Partners
        $agencies = Agency::all()->keyBy('name');

        foreach (['DA', 'BFAR', 'DAR'] as $agencyName) {
            $agency = $agencies->get($agencyName);
            if (!$agency) continue;

            User::updateOrCreate(
                ['email' => strtolower($agencyName) . '@partner.com'],
                [
                    'name'      => $agencyName . ' Partner User',
                    'password'  => Hash::make('Partner@1234'),
                    'role'      => 'partner',
                    'agency_id' => $agency->id,
                ]
            );
        }
    }
}
