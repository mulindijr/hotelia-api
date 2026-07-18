<?php

namespace App\Events\Guests;

use App\Models\Guest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GuestUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Guest $guest
    ) {}
}
