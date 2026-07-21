<?php

namespace App\Policies;

use App\Models\Hotel;
use App\Models\Service;
use App\Models\User;

class ServicePolicy
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
     * Determine whether the user can view the list of services for a hotel.
     */
    public function viewAny(User $user, Hotel $hotel): bool
    {
        return $user->belongsToHotel($hotel);
    }

    /**
     * Determine whether the user can create a service for a hotel.
     */
    public function create(User $user, Hotel $hotel): bool
    {
        return $user->belongsToHotel($hotel);
    }

    /**
     * Determine whether the user can view the service.
     */
    public function view(User $user, Service $service, ?Hotel $hotel = null): bool
    {
        if ($hotel && (int) $service->hotel_id !== (int) $hotel->id) {
            return false;
        }

        return $user->belongsToHotel($service->hotel_id);
    }

    /**
     * Determine whether the user can update the service.
     */
    public function update(User $user, Service $service, ?Hotel $hotel = null): bool
    {
        if ($hotel && (int) $service->hotel_id !== (int) $hotel->id) {
            return false;
        }

        return $user->belongsToHotel($service->hotel_id);
    }

    /**
     * Determine whether the user can delete the service.
     */
    public function delete(User $user, Service $service, ?Hotel $hotel = null): bool
    {
        if ($hotel && (int) $service->hotel_id !== (int) $hotel->id) {
            return false;
        }

        return $user->belongsToHotel($service->hotel_id);
    }
}
