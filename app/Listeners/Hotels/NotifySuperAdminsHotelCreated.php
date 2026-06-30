<?php

namespace App\Listeners\Hotels;

use App\Events\Hotels\HotelCreated;
use App\Models\User;
use App\Notifications\Hotels\NewHotelCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Notification;

class NotifySuperAdminsHotelCreated implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    // Number of attempts
    public int $tries = 3;

    // Timeout in seconds
    public int $timeout = 120;

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    // Handle the event
    public function handle(HotelCreated $event): void
    {
        $hotel = $event->hotel->loadMissing('settings');

        User::role('super_admin')
            ->chunkById(500, function ($users) use ($hotel) {
                Notification::send($users, new NewHotelCreatedNotification($hotel));
            });
    }

    // Handle a failed job
    public function failed(HotelCreated $event, \Throwable $exception): void
    {
        logger()->error('Failed to notify admins about hotel creation.', [
            'listener' => self::class,
            'hotel_id' => $event->hotel->id,
            'hotel_name' => $event->hotel->name,
            'exception' => $exception->getMessage(),
        ]);
    }
}
