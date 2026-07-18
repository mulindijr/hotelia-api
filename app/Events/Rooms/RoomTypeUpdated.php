<?php

namespace App\Events\Rooms;

use App\Models\RoomType;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomTypeUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public RoomType $roomType
    ) {}
}
