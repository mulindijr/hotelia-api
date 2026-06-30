<?php

namespace App\Listeners\Hotels;

use App\Events\Hotels\HotelUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogHotelUpdated implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'audit';

    public function handle(HotelUpdated $event): void
    {
        activity('hotel')
            ->performedOn($event->hotel)
            ->event('hotel_updated')
            ->withProperties([
                'hotel_id' => $event->hotel->id,
                'hotel_name' => $event->hotel->name,
            ])
            ->log('Hotel updated');
    }
}
