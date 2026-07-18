<?php

namespace App\Policies;

use App\Models\MaintenanceRequest;
use App\Models\User;

class MaintenanceRequestPolicy
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
     * Determine whether the user can view the maintenance request.
     */
    public function view(User $user, MaintenanceRequest $request): bool
    {
        return $user->belongsToHotel($request->room->hotel_id);
    }

    /**
     * Determine whether the user can update the maintenance request.
     */
    public function update(User $user, MaintenanceRequest $request): bool
    {
        return $user->belongsToHotel($request->room->hotel_id);
    }

    /**
     * Determine whether the user can delete the maintenance request.
     */
    public function delete(User $user, MaintenanceRequest $request): bool
    {
        return $user->belongsToHotel($request->room->hotel_id);
    }
}
