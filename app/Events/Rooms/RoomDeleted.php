<?php

namespace App\Events\Rooms;

use App\Models\Room;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomDeleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Room $room
    ) {}
}
