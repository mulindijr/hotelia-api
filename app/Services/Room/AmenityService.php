<?php

namespace App\Services\Room;

use App\Events\Rooms\AmenityCreated;
use App\Events\Rooms\AmenityUpdated;
use App\Events\Rooms\AmenityDeleted;
use App\Models\Amenity;

class AmenityService
{
    /**
     * Create a new amenity.
     */
    public function create(array $data): Amenity
    {
        $amenity = Amenity::create($data);

        event(new AmenityCreated($amenity));

        return $amenity;
    }

    /**
     * Update an amenity.
     */
    public function update(Amenity $amenity, array $data): Amenity
    {
        $amenity->update($data);

        event(new AmenityUpdated($amenity));

        return $amenity->fresh();
    }

    /**
     * Delete an amenity.
     */
    public function delete(Amenity $amenity): bool
    {
        event(new AmenityDeleted($amenity));

        return $amenity->delete();
    }
}
