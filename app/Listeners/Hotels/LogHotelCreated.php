<?php

namespace App\Listeners\Hotels;

use App\Events\Hotels\HotelCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogHotelCreated implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'audit';

    public function handle(HotelCreated $event): void
    {
        activity('hotel')
            ->performedOn($event->hotel)
            ->event('hotel_created')
            ->withProperties([
                'hotel_id' => $event->hotel->id,
                'hotel_name' => $event->hotel->name,
            ])
            ->log('Hotel created');
    }
}
