<?php

namespace App\Listeners\Hotels;

use App\Events\Hotels\HotelUpdated;
use App\Constants\Roles;
use App\Models\User;
use App\Notifications\Hotels\HotelUpdatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class NotifySuperAdminsHotelUpdated implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public int $tries = 3;

    public int $timeout = 120;

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    /**
     * Handle the event.
     */
    public function handle(HotelUpdated $event): void
    {
        $hotel = $event->hotel->loadMissing('settings');

        User::role(Roles::SUPER_ADMIN)
            ->chunkById(500, function ($users) use ($hotel) {

                Notification::send(
                    $users,
                    new HotelUpdatedNotification($hotel)
                );
            });
    }

    public function failed(HotelUpdated $event, \Throwable $exception): void
    {
        logger()->error(
            'Hotel updated notification failed.',
            [
                'listener' => self::class,
                'hotel_id' => $event->hotel->id,
                'hotel_name' => $event->hotel->name,
                'exception' => $exception->getMessage(),
            ]
        );
    }
}
