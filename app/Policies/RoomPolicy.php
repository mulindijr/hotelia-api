<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;

class RoomPolicy
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
     * Determine whether the user can view the room.
     */
    public function view(User $user, Room $room): bool
    {
        return $user->belongsToHotel($room->hotel_id);
    }

    /**
     * Determine whether the user can update the room.
     */
    public function update(User $user, Room $room): bool
    {
        return $user->belongsToHotel($room->hotel_id);
    }

    /**
     * Determine whether the user can delete the room.
     */
    public function delete(User $user, Room $room): bool
    {
        return $user->belongsToHotel($room->hotel_id);
    }
}
