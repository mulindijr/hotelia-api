<?php

namespace Tests\Feature\Api\V1\Rooms;

use App\Models\Amenity;
use App\Models\Hotel;
use App\Models\RoomType;
use Tests\ApiTestCase;
use Tests\Traits\InteractsWithHotels;

class RoomTypeTest extends ApiTestCase
{
    use InteractsWithHotels;

    /**
     * Test listing room types.
     */
    public function test_user_can_list_assigned_hotel_room_types(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        RoomType::factory()->count(2)->create(['hotel_id' => $hotel->id]);

        $response = $this->getJson(route('room-types.index', $hotel));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    /**
     * Test user cannot list room types of unassigned hotel.
     */
    public function test_user_cannot_list_unassigned_hotel_room_types(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = Hotel::factory()->create(); // Unassigned hotel

        $response = $this->getJson(route('room-types.index', $hotel));

        $response->assertStatus(403);
    }

    /**
     * Test creating a room type with amenities synchronization.
     */
    public function test_user_can_create_room_type_with_amenities(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $amenities = Amenity::factory()->count(2)->create();

        $payload = [
            'name' => 'Presidential Suite',
            'description' => 'Top floor suite with views.',
            'capacity' => 4,
            'beds' => 2,
            'base_price' => 450.00,
            'amenity_ids' => $amenities->pluck('id')->toArray(),
        ];

        $response = $this->postJson(route('room-types.store', $hotel), $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Presidential Suite')
            ->assertJsonCount(2, 'data.amenities');

        $this->assertDatabaseHas('room_types', [
            'hotel_id' => $hotel->id,
            'name' => 'Presidential Suite',
        ]);
    }
}
