<?php

namespace App\Events\Rooms;

use App\Models\Amenity;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AmenityUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Amenity $amenity
    ) {}
}
