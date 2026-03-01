<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ItemPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Item $item): bool
    {
        return $user->role === 'admin' || $user->role === 'staff' || $user->id === $item->reported_by;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Item $item): bool
    {
        // staff and admin can always delete
        if ($user->role === 'admin' || $user->role === 'staff') {
            return true;
        }

        // user can only delete their own item if it's pending
        return $user->id === $item->reported_by && $item->status === 'pending';
    }

    // other methods aren't strictly needed for this refactor but can be left as false or true
    public function viewAny(User $user): bool
    {
        return true;
    }
    public function view(User $user, Item $item): bool
    {
        return true;
    }
    public function create(User $user): bool
    {
        return true;
    }
    public function restore(User $user, Item $item): bool
    {
        return false;
    }
    public function forceDelete(User $user, Item $item): bool
    {
        return false;
    }
}
