<?php

namespace App\Policies;

use App\Models\Hotel;
use App\Models\MaintenanceRequest;
use App\Models\User;

class MaintenanceRequestPolicy
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
     * Determine whether the user can view the list of maintenance requests for a hotel.
     */
    public function viewAny(User $user, Hotel $hotel): bool
    {
        return $user->belongsToHotel($hotel);
    }

    /**
     * Determine whether the user can create a maintenance request for a hotel.
     */
    public function create(User $user, Hotel $hotel): bool
    {
        return $user->belongsToHotel($hotel);
    }

    /**
     * Determine whether the user can view the maintenance request.
     */
    public function view(User $user, MaintenanceRequest $request, ?Hotel $hotel = null): bool
    {
        $requestHotelId = $request->room?->hotel_id;
        if ($hotel && (int) $requestHotelId !== (int) $hotel->id) {
            return false;
        }

        return $requestHotelId ? $user->belongsToHotel($requestHotelId) : false;
    }

    /**
     * Determine whether the user can update the maintenance request.
     */
    public function update(User $user, MaintenanceRequest $request, ?Hotel $hotel = null): bool
    {
        $requestHotelId = $request->room?->hotel_id;
        if ($hotel && (int) $requestHotelId !== (int) $hotel->id) {
            return false;
        }

        return $requestHotelId ? $user->belongsToHotel($requestHotelId) : false;
    }

    /**
     * Determine whether the user can delete the maintenance request.
     */
    public function delete(User $user, MaintenanceRequest $request, ?Hotel $hotel = null): bool
    {
        $requestHotelId = $request->room?->hotel_id;
        if ($hotel && (int) $requestHotelId !== (int) $hotel->id) {
            return false;
        }

        return $requestHotelId ? $user->belongsToHotel($requestHotelId) : false;
    }
}
