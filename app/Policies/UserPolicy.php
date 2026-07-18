<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
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
     * Determine whether the user can view the target user.
     */
    public function view(User $user, User $targetUser): bool
    {
        $targetHotels = $targetUser->hotels()->pluck('hotels.id')->toArray();
        foreach ($targetHotels as $hotelId) {
            if ($user->belongsToHotel($hotelId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can update the target user.
     */
    public function update(User $user, User $targetUser): bool
    {
        if ($targetUser->hasRole('super_admin')) {
            return false;
        }

        $targetHotels = $targetUser->hotels()->pluck('hotels.id')->toArray();
        foreach ($targetHotels as $hotelId) {
            if ($user->belongsToHotel($hotelId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can delete the target user.
     */
    public function delete(User $user, User $targetUser): bool
    {
        if ($targetUser->hasRole('super_admin')) {
            return false;
        }

        $targetHotels = $targetUser->hotels()->pluck('hotels.id')->toArray();
        foreach ($targetHotels as $hotelId) {
            if ($user->belongsToHotel($hotelId)) {
                return true;
            }
        }

        return false;
    }
}
