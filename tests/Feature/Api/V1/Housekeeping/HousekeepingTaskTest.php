<?php

namespace Tests\Feature\Api\V1\Housekeeping;

use App\Models\Hotel;
use App\Models\HousekeepingTask;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Tests\ApiTestCase;
use Tests\Traits\InteractsWithHotels;

class HousekeepingTaskTest extends ApiTestCase
{
    use InteractsWithHotels;

    /**
     * Test listing housekeeping tasks.
     */
    public function test_user_can_view_housekeeping_tasks_list(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
        ]);

        HousekeepingTask::factory()->count(2)->create([
            'room_id' => $room->id,
        ]);

        $response = $this->getJson(route('housekeeping.index', $hotel));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    /**
     * Test security scoping.
     */
    public function test_user_cannot_view_unassigned_hotel_housekeeping_tasks(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = Hotel::factory()->create(); // Unassigned hotel

        $response = $this->getJson(route('housekeeping.index', $hotel));

        $response->assertStatus(403);
    }

    /**
     * Test creating a housekeeping task.
     */
    public function test_user_can_create_housekeeping_task(): void
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
            'status' => 'pending',
            'scheduled_at' => now()->addDay()->toDateString(),
        ];

        $response = $this->postJson(route('housekeeping.store', $hotel), $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.room_id', $room->id);

        $this->assertDatabaseHas('housekeeping_tasks', [
            'room_id' => $room->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Test validation prevents assigning unassociated housekeeper.
     */
    public function test_validation_prevents_assigning_unassociated_housekeeper(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
        ]);

        // Create a user who does not belong to this hotel
        $externalUser = User::factory()->create();

        $payload = [
            'room_id' => $room->id,
            'assigned_to' => $externalUser->id,
            'scheduled_at' => now()->addDay()->toDateString(),
        ];

        $response = $this->postJson(route('housekeeping.store', $hotel), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['assigned_to']);
    }

    /**
     * Test updating a housekeeping task.
     */
    public function test_user_can_update_housekeeping_task(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
        ]);

        $task = HousekeepingTask::factory()->create([
            'room_id' => $room->id,
            'status' => 'pending',
        ]);

        $payload = [
            'status' => 'completed',
        ];

        $response = $this->putJson(route('housekeeping.update', [$hotel, $task]), $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonNotNull('data.completed_at');

        $this->assertDatabaseHas('housekeeping_tasks', [
            'id' => $task->id,
            'status' => 'completed',
        ]);
    }
}
