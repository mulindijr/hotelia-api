<?php

namespace App\Policies;

use App\Models\Hotel;
use App\Models\HotelSetting;
use App\Models\User;

class HotelSettingPolicy
{
    /**
     * Before hook to allow Super Admins full access.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view the hotel settings.
     */
    public function view(User $user, HotelSetting $setting, ?Hotel $hotel = null): bool
    {
        if ($hotel && (int) $setting->hotel_id !== (int) $hotel->id) {
            return false;
        }

        return $user->belongsToHotel($setting->hotel_id);
    }

    /**
     * Determine whether the user can update the hotel settings.
     */
    public function update(User $user, HotelSetting $setting, ?Hotel $hotel = null): bool
    {
        if ($hotel && (int) $setting->hotel_id !== (int) $hotel->id) {
            return false;
        }

        return $user->belongsToHotel($setting->hotel_id);
    }
}
