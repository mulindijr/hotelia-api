<?php

namespace App\Listeners\Hotels;

use App\Events\Hotels\HotelDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupHotelResources implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'cleanup';

    public int $tries = 3;

    public int $timeout = 120;

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handle(HotelDeleted $event): void
    {
        if (
            $event->hotel->logo &&
            Storage::disk('public')->exists($event->hotel->logo)
        ) {
            Storage::disk('public')->delete($event->hotel->logo);
        }
    }

    public function failed(HotelDeleted $event, \Throwable $exception): void {
        Log::error(
            'Hotel cleanup failed.',
            [
                'listener' => self::class,
                'hotel_id' => $event->hotel->id,
                'hotel_name' => $event->hotel->name,
                'exception' => $exception->getMessage(),
            ]
        );
    }
}
