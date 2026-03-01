<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Claim;
use App\Models\Item;
use App\Models\User;

class ClaimSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('role', 'user')->first();
        $item = Item::first();

        if (!$user || !$item)
            return;

        Claim::create([
            'item_id' => $item->id,
            'claimer_id' => $user->id,
            'status' => 'pending',
            'proof_details' => 'This belongs to me.',
        ]);
    }
}