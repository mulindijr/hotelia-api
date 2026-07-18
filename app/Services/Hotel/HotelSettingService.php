<?php

namespace App\Services\Hotel;

use App\Events\Hotels\HotelSettingUpdated;
use App\Models\HotelSetting;

class HotelSettingService
{
    /**
     * Update an existing hotel settings record.
     */
    public function update(HotelSetting $setting, array $data): HotelSetting
    {
        $setting->update($data);

        event(new HotelSettingUpdated($setting));

        return $setting->fresh();
    }
}
