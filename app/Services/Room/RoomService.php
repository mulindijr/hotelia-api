<?php

namespace App\Services\Room;

use App\Events\Rooms\RoomCreated;
use App\Events\Rooms\RoomDeleted;
use App\Events\Rooms\RoomUpdated;
use App\Models\Hotel;
use App\Models\Room;

class RoomService
{
    /**
     * Create a new room for a hotel.
     */
    public function create(Hotel $hotel, array $data): Room
    {
        $data['status'] = $data['status'] ?? 'available';

        /** @var Room $room */
        $room = $hotel->rooms()->create($data);

        event(new RoomCreated($room));

        return $room->load('roomType');
    }

    /**
     * Update a room's attributes or status.
     */
    public function update(Room $room, array $data): Room
    {
        $room->update($data);

        event(new RoomUpdated($room));

        return $room->fresh()->load('roomType');
    }

    /**
     * Delete a room.
     */
    public function delete(Room $room): bool
    {
        event(new RoomDeleted($room));

        return $room->delete();
    }
}
