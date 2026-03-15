<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@ffprams.com'],
            [
                'name'     => 'System Admin',
                'password' => Hash::make('Admin@1234'),
                'role'     => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff@ffprams.com'],
            [
                'name'     => 'LGU Staff',
                'password' => Hash::make('Staff@1234'),
                'role'     => 'staff',
            ]
        );
    }
}
