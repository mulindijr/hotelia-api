<?php

namespace App\Events\Bookings;

use App\Models\Booking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingCancelled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Booking $booking
    ) {}
}
