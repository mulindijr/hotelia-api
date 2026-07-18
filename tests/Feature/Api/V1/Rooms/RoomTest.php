<?php

namespace Tests\Feature\Api\V1\Rooms;

use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomType;
use Tests\ApiTestCase;
use Tests\Traits\InteractsWithHotels;

class RoomTest extends ApiTestCase
{
    use InteractsWithHotels;

    /**
     * Test listing hotel rooms.
     */
    public function test_user_can_list_assigned_hotel_rooms(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);
        Room::factory()->count(2)->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
        ]);

        $response = $this->getJson(route('rooms.index', $hotel));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    /**
     * Test creating a room.
     */
    public function test_user_can_create_room(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);

        $payload = [
            'room_type_id' => $roomType->id,
            'room_number' => 'Suite 101',
            'floor' => 1,
            'status' => 'available',
        ];

        $response = $this->postJson(route('rooms.store', $hotel), $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.room_number', 'Suite 101');

        $this->assertDatabaseHas('rooms', [
            'hotel_id' => $hotel->id,
            'room_number' => 'Suite 101',
        ]);
    }

    /**
     * Test room validation prevents linking a room type from another hotel.
     */
    public function test_room_validation_prevents_unassociated_room_type(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);
        
        $otherHotel = Hotel::factory()->create();
        $otherRoomType = RoomType::factory()->create(['hotel_id' => $otherHotel->id]);

        $payload = [
            'room_type_id' => $otherRoomType->id,
            'room_number' => '102',
        ];

        $response = $this->postJson(route('rooms.store', $hotel), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['room_type_id']);
    }
}
