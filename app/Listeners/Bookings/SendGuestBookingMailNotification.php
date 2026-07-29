<?php

namespace App\Listeners\Bookings;

use App\Constants\BookingStatus;
use App\Events\Bookings\BookingCancelled;
use App\Events\Bookings\BookingCreated;
use App\Events\Bookings\BookingUpdated;
use App\Mail\Bookings\BookingCancelledMail;
use App\Mail\Bookings\BookingConfirmationMail;
use App\Mail\Bookings\BookingNoShowMail;
use App\Mail\Bookings\BookingUpdatedMail;
use Illuminate\Support\Facades\Mail;

class SendGuestBookingMailNotification
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $booking = $event->booking;
        $guest = $booking->guest;

        if (! $guest || ! $guest->email) {
            return;
        }

        // Eager load details needed by templates
        $booking->loadMissing(['hotel.settings', 'guest', 'rooms.roomType']);

        if ($event instanceof BookingCreated) {
            Mail::to($guest->email)->send(new BookingConfirmationMail($booking));
        } elseif ($event instanceof BookingCancelled) {
            Mail::to($guest->email)->send(new BookingCancelledMail($booking));
        } elseif ($event instanceof BookingUpdated) {
            if ($booking->status === BookingStatus::NO_SHOW) {
                Mail::to($guest->email)->send(new BookingNoShowMail($booking));
            } else {
                Mail::to($guest->email)->send(new BookingUpdatedMail($booking));
            }
        }
    }
}
