<?php

namespace App\Events\Rooms;

use App\Models\Room;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Room $room
    ) {}
}
