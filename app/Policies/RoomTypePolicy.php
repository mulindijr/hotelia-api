<?php

namespace App\Policies;

use App\Models\RoomType;
use App\Models\User;

class RoomTypePolicy
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
     * Determine whether the user can view the specific room type.
     */
    public function view(User $user, RoomType $roomType): bool
    {
        return $user->belongsToHotel($roomType->hotel_id);
    }

    /**
     * Determine whether the user can update the room type.
     */
    public function update(User $user, RoomType $roomType): bool
    {
        return $user->belongsToHotel($roomType->hotel_id);
    }

    /**
     * Determine whether the user can delete the room type.
     */
    public function delete(User $user, RoomType $roomType): bool
    {
        return $user->belongsToHotel($roomType->hotel_id);
    }
}
