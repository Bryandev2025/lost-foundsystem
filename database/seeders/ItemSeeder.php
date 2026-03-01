<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\User;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('role', 'user')->first();

        if (!$user)
            return;

        Item::create([
            'type' => 'lost',
            'title' => 'Black Wallet',
            'description' => 'Leather wallet with ID',
            'category' => 'Personal',
            'brand' => 'Nike',
            'color' => 'Black',
            'location' => 'Library',
            'status' => 'pending',
            'reported_by' => $user->id,
        ]);

        Item::create([
            'type' => 'found',
            'title' => 'Samsung Phone',
            'description' => 'Found near cafeteria',
            'category' => 'Electronics',
            'brand' => 'Samsung',
            'color' => 'Blue',
            'location' => 'Cafeteria',
            'status' => 'pending',
            'reported_by' => $user->id,
            'found_by' => $user->id,
        ]);
    }
}