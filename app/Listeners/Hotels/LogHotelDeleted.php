<?php

namespace App\Listeners\Hotels;

use App\Events\Hotels\HotelDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogHotelDeleted implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'audit';

    public function handle(HotelDeleted $event): void
    {
        activity('hotel')
            ->performedOn($event->hotel)
            ->event('hotel_deleted')
            ->withProperties([
                'hotel_id' => $event->hotel->id,
                'hotel_name' => $event->hotel->name,
            ])
            ->log('Hotel deleted');
    }
}
