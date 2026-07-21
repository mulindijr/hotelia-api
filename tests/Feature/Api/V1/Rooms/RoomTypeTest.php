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

    /**
     * Test creating a room type for unassigned hotel is forbidden.
     */
    public function test_user_cannot_create_room_type_for_unassigned_hotel(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = Hotel::factory()->create(); // Unassigned hotel

        $payload = [
            'name' => 'Deluxe Suite',
            'capacity' => 2,
            'beds' => 1,
            'base_price' => 200.00,
        ];

        $response = $this->postJson(route('room-types.store', $hotel), $payload);

        $response->assertStatus(403);
    }

    /**
     * Test viewing details of assigned hotel room type.
     */
    public function test_user_can_show_assigned_hotel_room_type(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);

        $response = $this->getJson(route('room-types.show', [$hotel, $roomType]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $roomType->id);
    }

    /**
     * Test user cannot view room type of unassigned hotel.
     */
    public function test_user_cannot_show_unassigned_hotel_room_type(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $unassignedHotel = Hotel::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $unassignedHotel->id]);

        $response = $this->getJson(route('room-types.show', [$unassignedHotel, $roomType]));

        $response->assertStatus(403);
    }

    /**
     * Test scoped route model binding prevents accessing cross-hotel room types (returns 404).
     */
    public function test_scoped_route_model_binding_prevents_access_to_cross_hotel_room_type(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotelA = $this->createHotelForUser($user);
        $hotelB = Hotel::factory()->create();

        // Room type belongs to Hotel B
        $roomTypeB = RoomType::factory()->create(['hotel_id' => $hotelB->id]);

        // Attempting to resolve RoomType B under Hotel A route endpoint must fail with 404
        $response = $this->getJson(route('room-types.show', [$hotelA, $roomTypeB]));

        $response->assertNotFound();
    }

    /**
     * Test updating assigned hotel room type.
     */
    public function test_user_can_update_assigned_hotel_room_type(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id, 'name' => 'Old Name']);

        $response = $this->putJson(route('room-types.update', [$hotel, $roomType]), [
            'name' => 'Updated Name',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Updated Name');
    }

    /**
     * Test updating room type of unassigned hotel is forbidden.
     */
    public function test_user_cannot_update_unassigned_hotel_room_type(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $unassignedHotel = Hotel::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $unassignedHotel->id]);

        $response = $this->putJson(route('room-types.update', [$unassignedHotel, $roomType]), [
            'name' => 'Forbidden Update',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test super admin can delete room type.
     */
    public function test_super_admin_can_delete_hotel_room_type(): void
    {
        $user = $this->actingAsRole('super_admin');
        $hotel = Hotel::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);

        $response = $this->deleteJson(route('room-types.destroy', [$hotel, $roomType]));

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('room_types', ['id' => $roomType->id]);
    }

    /**
     * Test deleting room type of unassigned hotel is forbidden.
     */
    public function test_user_cannot_delete_unassigned_hotel_room_type(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $unassignedHotel = Hotel::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $unassignedHotel->id]);

        $response = $this->deleteJson(route('room-types.destroy', [$unassignedHotel, $roomType]));

        $response->assertStatus(403);
    }

    /**
     * Test super admin can access room types for any hotel.
     */
    public function test_super_admin_can_access_room_types_for_any_hotel(): void
    {
        $superAdmin = $this->actingAsRole('super_admin');
        $hotel = Hotel::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);

        $response = $this->getJson(route('room-types.show', [$hotel, $roomType]));

        $response->assertOk()
            ->assertJsonPath('success', true);
    }
}
