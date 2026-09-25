<?php

namespace App\Policies;

use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    /**
     * Before hook for Super Admins.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view the list of rooms for a hotel.
     */
    public function viewAny(User $user, Hotel $hotel): bool
    {
        return $user->belongsToHotel($hotel);
    }

    /**
     * Determine whether the user can create a room for a hotel.
     */
    public function create(User $user, Hotel $hotel): bool
    {
        return $user->belongsToHotel($hotel);
    }

    /**
     * Determine whether the user can view the room.
     */
    public function view(User $user, Room $room, ?Hotel $hotel = null): bool
    {
        if ($hotel && (int) $room->hotel_id !== (int) $hotel->id) {
            return false;
        }

        return $user->belongsToHotel($room->hotel_id);
    }

    /**
     * Determine whether the user can update the room.
     */
    public function update(User $user, Room $room, ?Hotel $hotel = null): bool
    {
        if ($hotel && (int) $room->hotel_id !== (int) $hotel->id) {
            return false;
        }

        return $user->belongsToHotel($room->hotel_id);
    }

    /**
     * Determine whether the user can delete the room.
     */
    public function delete(User $user, Room $room, ?Hotel $hotel = null): bool
    {
        if ($hotel && (int) $room->hotel_id !== (int) $hotel->id) {
            return false;
        }

        return $user->belongsToHotel($room->hotel_id);
    }
}
