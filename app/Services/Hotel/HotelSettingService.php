<?php

namespace App\Services\Hotel;

use App\Events\Hotels\HotelSettingUpdated;
use App\Models\Hotel;
use App\Models\HotelSetting;
use Illuminate\Support\Facades\Cache;

class HotelSettingService
{
    /**
     * Retrieve hotel settings with caching.
     */
    public function getSettings(Hotel $hotel): HotelSetting
    {
        return Cache::remember("hotel:{$hotel->id}:settings", 86400, function () use ($hotel) {
            return $hotel->settings ?: $hotel->settings()->create([
                'currency' => 'KES',
                'timezone' => 'Africa/Nairobi',
                'language' => 'en',
            ]);
        });
    }

    /**
     * Update an existing hotel settings record.
     */
    public function update(HotelSetting $setting, array $data): HotelSetting
    {
        $setting->update($data);

        Cache::forget("hotel:{$setting->hotel_id}:settings");

        event(new HotelSettingUpdated($setting));

        return $setting->fresh();
    }
}
