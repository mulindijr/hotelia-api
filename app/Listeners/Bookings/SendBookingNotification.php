<?php

namespace App\Listeners\Bookings;

use App\Notifications\Bookings\BookingNotification;
use Illuminate\Support\Facades\Notification;

class SendBookingNotification
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $booking = $event->booking;
        $className = class_basename($event);

        $action = match ($className) {
            'BookingCreated' => 'created',
            'BookingCancelled' => 'cancelled',
            'BookingCheckedIn' => 'checked_in',
            'BookingCheckedOut' => 'checked_out',
            default => 'updated',
        };

        $hotel = $booking->hotel;
        if (!$hotel) {
            return;
        }

        // Find all staff who are hotel managers or receptionists at the hotel
        $users = $hotel->users()->role(['hotel_manager', 'receptionist'])->get();

        if ($users->isNotEmpty()) {
            Notification::send($users, new BookingNotification($booking, $action));
        }
    }
}
