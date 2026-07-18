<?php

namespace App\Policies;

use App\Models\Amenity;
use App\Models\User;

class AmenityPolicy
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
     * Determine whether the user can view the amenities index.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the amenity.
     */
    public function view(User $user, Amenity $amenity): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create an amenity.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update an amenity.
     */
    public function update(User $user, Amenity $amenity): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete an amenity.
     */
    public function delete(User $user, Amenity $amenity): bool
    {
        return true;
    }
}
