<?php

namespace App\Policies;

use App\Models\HousekeepingTask;
use App\Models\User;

class HousekeepingTaskPolicy
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
     * Determine whether the user can view the housekeeping task.
     */
    public function view(User $user, HousekeepingTask $task): bool
    {
        return $user->belongsToHotel($task->room->hotel_id);
    }

    /**
     * Determine whether the user can update the housekeeping task.
     */
    public function update(User $user, HousekeepingTask $task): bool
    {
        return $user->belongsToHotel($task->room->hotel_id);
    }

    /**
     * Determine whether the user can delete the housekeeping task.
     */
    public function delete(User $user, HousekeepingTask $task): bool
    {
        return $user->belongsToHotel($task->room->hotel_id);
    }
}
