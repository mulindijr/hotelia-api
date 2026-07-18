<?php

namespace App\Listeners\Maintenance;

use App\Notifications\Maintenance\MaintenanceRequestNotification;
use Illuminate\Support\Facades\Notification;

class SendMaintenanceRequestNotification
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $request = $event->request;
        $room = $request->room;
        if (!$room) {
            return;
        }

        $hotel = $room->hotel;
        if (!$hotel) {
            return;
        }

        // Find all staff who are hotel managers at the hotel
        $users = $hotel->users()->role(['hotel_manager'])->get();

        if ($users->isNotEmpty()) {
            Notification::send($users, new MaintenanceRequestNotification($request));
        }
    }
}
