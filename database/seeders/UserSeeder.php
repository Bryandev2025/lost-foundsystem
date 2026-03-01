<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'user@test.com'],
            [
                'name' => 'Sample User',
                'phone' => '09123456781',
                'role' => 'user',
                'password' => Hash::make('password'),
            ]
        );
    }
}