<?php

namespace App\Policies;

use App\Models\Guest;
use App\Models\User;

class GuestPolicy
{
    /**
     * Before hook for Super Admins.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view the guests index.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the guest.
     */
    public function view(User $user, Guest $guest): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create a guest.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the guest.
     */
    public function update(User $user, Guest $guest): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the guest.
     */
    public function delete(User $user, Guest $guest): bool
    {
        return true;
    }
}
