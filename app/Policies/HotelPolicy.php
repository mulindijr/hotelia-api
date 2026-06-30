<?php

namespace App\Policies;

use App\Models\Hotel;
use App\Models\User;

class HotelPolicy
{
    /**
     * Before hook.
     *
     * Super Admin bypasses every authorization check.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view the hotel listing.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a specific hotel.
     */
    public function view(User $user, Hotel $hotel): bool
    {
        return $user->belongsToHotel($hotel);
    }

    /**
     * Determine whether the user can create a hotel.
     * Permission middleware already checks "create hotels".
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update a hotel.
     */
    public function update(User $user, Hotel $hotel): bool
    {
        return $user->belongsToHotel($hotel);
    }

    /**
     * Determine whether the user can delete a hotel.
     */
    public function delete(User $user, Hotel $hotel): bool
    {
        return $user->belongsToHotel($hotel);
    }

    /**
     * Determine whether the user can restore a hotel.
     */
    public function restore(User $user, Hotel $hotel): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete a hotel.
     */
    public function forceDelete(User $user, Hotel $hotel): bool
    {
        return false;
    }
}
