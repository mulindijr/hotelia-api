<?php

namespace App\Listeners\Hotels;

use App\Events\Hotels\HotelSettingUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;

class LogHotelSettingUpdated implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(HotelSettingUpdated $event): void
    {
        // Flush settings cache for the specific hotel to ensure new defaults are loaded
        Cache::forget("hotel:{$event->setting->hotel_id}:settings");
    }
}
