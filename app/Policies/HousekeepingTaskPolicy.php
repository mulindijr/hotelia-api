<?php

namespace App\Policies;

use App\Models\Hotel;
use App\Models\HousekeepingTask;
use App\Models\User;

class HousekeepingTaskPolicy
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
     * Determine whether the user can view the list of housekeeping tasks for a hotel.
     */
    public function viewAny(User $user, Hotel $hotel): bool
    {
        return $user->belongsToHotel($hotel);
    }

    /**
     * Determine whether the user can create a housekeeping task for a hotel.
     */
    public function create(User $user, Hotel $hotel): bool
    {
        return $user->belongsToHotel($hotel);
    }

    /**
     * Determine whether the user can view the housekeeping task.
     */
    public function view(User $user, HousekeepingTask $task, ?Hotel $hotel = null): bool
    {
        $taskHotelId = $task->room?->hotel_id;
        if ($hotel && (int) $taskHotelId !== (int) $hotel->id) {
            return false;
        }

        return $taskHotelId ? $user->belongsToHotel($taskHotelId) : false;
    }

    /**
     * Determine whether the user can update the housekeeping task.
     */
    public function update(User $user, HousekeepingTask $task, ?Hotel $hotel = null): bool
    {
        $taskHotelId = $task->room?->hotel_id;
        if ($hotel && (int) $taskHotelId !== (int) $hotel->id) {
            return false;
        }

        return $taskHotelId ? $user->belongsToHotel($taskHotelId) : false;
    }

    /**
     * Determine whether the user can delete the housekeeping task.
     */
    public function delete(User $user, HousekeepingTask $task, ?Hotel $hotel = null): bool
    {
        $taskHotelId = $task->room?->hotel_id;
        if ($hotel && (int) $taskHotelId !== (int) $hotel->id) {
            return false;
        }

        return $taskHotelId ? $user->belongsToHotel($taskHotelId) : false;
    }
}
