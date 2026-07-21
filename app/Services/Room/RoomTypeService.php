<?php

namespace App\Services\Room;

use App\Events\Rooms\RoomTypeCreated;
use App\Events\Rooms\RoomTypeUpdated;
use App\Events\Rooms\RoomTypeDeleted;
use App\Models\Hotel;
use App\Models\RoomType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RoomTypeService
{
    /**
     * Retrieve room types for a hotel with caching.
     */
    public function getRoomTypes(Hotel $hotel)
    {
        return Cache::remember("hotel:{$hotel->id}:room_types", 3600, function () use ($hotel) {
            return $hotel->roomTypes()->with('amenities')->get();
        });
    }

    /**
     * Create a new room type for a hotel.
     */
    public function create(Hotel $hotel, array $data): RoomType
    {
        return DB::transaction(function () use ($hotel, $data) {
            $amenityIds = $data['amenity_ids'] ?? [];
            unset($data['amenity_ids']);

            /** @var RoomType $roomType */
            $roomType = $hotel->roomTypes()->create($data);

            if (!empty($amenityIds)) {
                $roomType->amenities()->sync($amenityIds);
            }

            Cache::forget("hotel:{$hotel->id}:room_types");

            event(new RoomTypeCreated($roomType));

            return $roomType->load('amenities');
        });
    }

    /**
     * Update an existing room type.
     */
    public function update(RoomType $roomType, array $data): RoomType
    {
        return DB::transaction(function () use ($roomType, $data) {
            $hasAmenities = array_key_exists('amenity_ids', $data);
            $amenityIds = $data['amenity_ids'] ?? [];
            unset($data['amenity_ids']);

            $roomType->update($data);

            if ($hasAmenities) {
                $roomType->amenities()->sync($amenityIds);
            }

            Cache::forget("hotel:{$roomType->hotel_id}:room_types");

            event(new RoomTypeUpdated($roomType));

            return $roomType->fresh()->load('amenities');
        });
    }

    /**
     * Delete a room type.
     */
    public function delete(RoomType $roomType): bool
    {
        return DB::transaction(function () use ($roomType) {
            Cache::forget("hotel:{$roomType->hotel_id}:room_types");

            event(new RoomTypeDeleted($roomType));

            return $roomType->delete();
        });
    }
}
