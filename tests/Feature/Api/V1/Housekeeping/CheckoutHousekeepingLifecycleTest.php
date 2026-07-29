<?php

namespace Tests\Feature\Api\V1\Housekeeping;

use App\Constants\BookingStatus;
use App\Constants\RoomStatus;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\HousekeepingTask;
use App\Models\MaintenanceRequest;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\Booking\BookingService;
use App\Services\Housekeeping\HousekeepingService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutHousekeepingLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_checkout_auto_creates_pending_housekeeping_task(): void
    {
        $hotel = Hotel::factory()->create();
        $guest = Guest::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);
        $room = Room::factory()->create(['hotel_id' => $hotel->id, 'room_type_id' => $roomType->id, 'status' => RoomStatus::OCCUPIED]);

        $booking = Booking::create([
            'booking_reference' => 'BK-CHKOUT01',
            'hotel_id' => $hotel->id,
            'guest_id' => $guest->id,
            'check_in_date' => now()->subDays(2)->toDateString(),
            'check_out_date' => now()->toDateString(),
            'total_amount' => 200.00,
            'status' => BookingStatus::CHECKED_IN,
        ]);
        $booking->rooms()->attach($room->id, ['price_per_night' => 100.00]);

        $bookingService = app(BookingService::class);
        $bookingService->checkOut($booking);

        $this->assertEquals(BookingStatus::CHECKED_OUT, $booking->fresh()->status);
        $this->assertEquals(RoomStatus::CLEANING, $room->fresh()->status);

        // Assert pending housekeeping task was auto-created
        $task = HousekeepingTask::where('room_id', $room->id)->first();
        $this->assertNotNull($task);
        $this->assertEquals('pending', $task->status);
    }

    public function test_completing_task_releases_room_to_available_when_no_blockers(): void
    {
        $hotel = Hotel::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);
        $room = Room::factory()->create(['hotel_id' => $hotel->id, 'room_type_id' => $roomType->id, 'status' => RoomStatus::CLEANING]);

        $task = HousekeepingTask::create([
            'room_id' => $room->id,
            'status' => 'pending',
            'scheduled_at' => now(),
        ]);

        $housekeepingService = app(HousekeepingService::class);
        $housekeepingService->update($task, ['status' => 'completed']);

        $this->assertEquals('completed', $task->fresh()->status);
        $this->assertEquals(RoomStatus::AVAILABLE, $room->fresh()->status);
    }

    public function test_completing_task_transitions_room_to_maintenance_if_active_maintenance_request_exists(): void
    {
        $hotel = Hotel::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);
        $room = Room::factory()->create(['hotel_id' => $hotel->id, 'room_type_id' => $roomType->id, 'status' => RoomStatus::CLEANING]);

        // Active maintenance request on room
        MaintenanceRequest::create([
            'room_id' => $room->id,
            'description' => 'Leaking faucet',
            'status' => 'in_progress',
            'priority' => 'high',
        ]);

        $task = HousekeepingTask::create([
            'room_id' => $room->id,
            'status' => 'pending',
            'scheduled_at' => now(),
        ]);

        $housekeepingService = app(HousekeepingService::class);
        $housekeepingService->update($task, ['status' => 'completed']);

        $this->assertEquals('completed', $task->fresh()->status);
        $this->assertEquals(RoomStatus::MAINTENANCE, $room->fresh()->status);
    }
}
