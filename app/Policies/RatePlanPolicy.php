<?php

namespace App\Policies;

use App\Constants\Permissions;
use App\Models\Hotel;
use App\Models\RatePlan;
use App\Models\User;

class RatePlanPolicy
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

    public function viewAny(User $user, Hotel $hotel): bool
    {
        return $user->belongsToHotel($hotel) && $user->hasPermissionTo(Permissions::VIEW_RATE_PLANS);
    }

    public function create(User $user, Hotel $hotel): bool
    {
        return $user->belongsToHotel($hotel) && $user->hasPermissionTo(Permissions::MANAGE_RATE_PLANS);
    }

    public function view(User $user, RatePlan $ratePlan, ?Hotel $hotel = null): bool
    {
        if ($hotel && (int) $ratePlan->hotel_id !== (int) $hotel->id) {
            return false;
        }

        return $user->belongsToHotel($ratePlan->hotel_id) && $user->hasPermissionTo(Permissions::VIEW_RATE_PLANS);
    }

    public function update(User $user, RatePlan $ratePlan, ?Hotel $hotel = null): bool
    {
        if ($hotel && (int) $ratePlan->hotel_id !== (int) $hotel->id) {
            return false;
        }

        return $user->belongsToHotel($ratePlan->hotel_id) && $user->hasPermissionTo(Permissions::MANAGE_RATE_PLANS);
    }

    public function delete(User $user, RatePlan $ratePlan, ?Hotel $hotel = null): bool
    {
        if ($hotel && (int) $ratePlan->hotel_id !== (int) $hotel->id) {
            return false;
        }

        return $user->belongsToHotel($ratePlan->hotel_id) && $user->hasPermissionTo(Permissions::MANAGE_RATE_PLANS);
    }
}
