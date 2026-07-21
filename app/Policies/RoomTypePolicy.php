<?php

namespace App\Policies;

use App\Models\Hotel;
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
     * Determine whether the user can view the list of room types for a hotel.
     */
    public function viewAny(User $user, Hotel $hotel): bool
    {
        return $user->belongsToHotel($hotel);
    }

    /**
     * Determine whether the user can create a room type for a hotel.
     */
    public function create(User $user, Hotel $hotel): bool
    {
        return $user->belongsToHotel($hotel);
    }

    /**
     * Determine whether the user can view the specific room type.
     */
    public function view(User $user, RoomType $roomType, ?Hotel $hotel = null): bool
    {
        if ($hotel && (int) $roomType->hotel_id !== (int) $hotel->id) {
            return false;
        }

        return $user->belongsToHotel($roomType->hotel_id);
    }

    /**
     * Determine whether the user can update the room type.
     */
    public function update(User $user, RoomType $roomType, ?Hotel $hotel = null): bool
    {
        if ($hotel && (int) $roomType->hotel_id !== (int) $hotel->id) {
            return false;
        }

        return $user->belongsToHotel($roomType->hotel_id);
    }

    /**
     * Determine whether the user can delete the room type.
     */
    public function delete(User $user, RoomType $roomType, ?Hotel $hotel = null): bool
    {
        if ($hotel && (int) $roomType->hotel_id !== (int) $hotel->id) {
            return false;
        }

        return $user->belongsToHotel($roomType->hotel_id);
    }
}
