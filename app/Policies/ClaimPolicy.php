<?php

namespace App\Policies;

use App\Models\Claim;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClaimPolicy
{
    public function view(User $user, Claim $claim): bool
    {
        return $user->role === 'admin' || $user->role === 'staff' || $user->id === $claim->claimer_id;
    }

    public function updateStatus(User $user, Claim $claim): bool
    {
        return $user->role === 'admin' || $user->role === 'staff';
    }

    // others
    public function viewAny(User $user): bool
    {
        return true;
    }
    public function create(User $user): bool
    {
        return true;
    }
    public function update(User $user, Claim $claim): bool
    {
        return false;
    }
    public function delete(User $user, Claim $claim): bool
    {
        return false;
    }
    public function restore(User $user, Claim $claim): bool
    {
        return false;
    }
    public function forceDelete(User $user, Claim $claim): bool
    {
        return false;
    }
}
