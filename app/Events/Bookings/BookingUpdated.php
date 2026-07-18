<?php

namespace App\Events\Bookings;

use App\Models\Booking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Booking $booking
    ) {}
}
