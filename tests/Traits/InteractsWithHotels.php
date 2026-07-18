<?php

namespace Tests\Traits;

use App\Models\Hotel;
use App\Models\User;

trait InteractsWithHotels
{
    /**
     * Create a hotel and associate it with a user.
     */
    protected function createHotelForUser(User $user, array $hotelAttributes = []): Hotel
    {
        $hotel = Hotel::factory()->create($hotelAttributes);
        $hotel->users()->attach($user->id);
        
        return $hotel;
    }
}
