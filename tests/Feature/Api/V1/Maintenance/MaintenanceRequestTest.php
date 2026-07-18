<?php

namespace Tests\Feature\Api\V1\Maintenance;

use App\Models\Hotel;
use App\Models\MaintenanceRequest;
use App\Models\Room;
use App\Models\RoomType;
use Tests\ApiTestCase;
use Tests\Traits\InteractsWithHotels;

class MaintenanceRequestTest extends ApiTestCase
{
    use InteractsWithHotels;

    /**
     * Test listing maintenance requests.
     */
    public function test_user_can_view_maintenance_requests_list(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
        ]);

        MaintenanceRequest::factory()->count(2)->create([
            'room_id' => $room->id,
        ]);

        $response = $this->getJson(route('maintenance.index', $hotel));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    /**
     * Test security scoping.
     */
    public function test_user_cannot_view_unassigned_hotel_maintenance_requests(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = Hotel::factory()->create(); // Unassigned hotel

        $response = $this->getJson(route('maintenance.index', $hotel));

        $response->assertStatus(403);
    }

    /**
     * Test creating a maintenance request.
     */
    public function test_user_can_create_maintenance_request(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
        ]);

        $payload = [
            'room_id' => $room->id,
            'description' => 'Light fixture flickering in bathroom.',
            'priority' => 'high',
        ];

        $response = $this->postJson(route('maintenance.store', $hotel), $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.room_id', $room->id)
            ->assertJsonPath('data.priority', 'high');

        $this->assertDatabaseHas('maintenance_requests', [
            'room_id' => $room->id,
            'description' => 'Light fixture flickering in bathroom.',
            'priority' => 'high',
            'status' => 'open',
        ]);
    }

    /**
     * Test validation prevents linking unassociated room.
     */
    public function test_validation_prevents_linking_unassociated_room(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        // Create a room that does not belong to this hotel
        $externalRoom = Room::factory()->create();

        $payload = [
            'room_id' => $externalRoom->id,
            'description' => 'Sink leaking.',
        ];

        $response = $this->postJson(route('maintenance.store', $hotel), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['room_id']);
    }

    /**
     * Test updating a maintenance request.
     */
    public function test_user_can_update_maintenance_request(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
        ]);

        $maintenanceRequest = MaintenanceRequest::factory()->create([
            'room_id' => $room->id,
            'status' => 'open',
        ]);

        $payload = [
            'status' => 'in_progress',
        ];

        $response = $this->putJson(route('maintenance.update', [$hotel, $maintenanceRequest]), $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'in_progress');

        $this->assertDatabaseHas('maintenance_requests', [
            'id' => $maintenanceRequest->id,
            'status' => 'in_progress',
        ]);
    }

    /**
     * Test deleting a maintenance request.
     */
    public function test_user_can_delete_maintenance_request(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
        ]);

        $maintenanceRequest = MaintenanceRequest::factory()->create([
            'room_id' => $room->id,
        ]);

        $response = $this->deleteJson(route('maintenance.destroy', [$hotel, $maintenanceRequest]));

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('maintenance_requests', [
            'id' => $maintenanceRequest->id,
        ]);
    }
}
