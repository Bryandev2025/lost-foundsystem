<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'staff@test.com'],
            [
                'name' => 'Lost and Found Staff',
                'phone' => '09123456780',
                'role' => 'staff',
                'password' => Hash::make('password'),
            ]
        );
    }
}