<?php

namespace Tests\Feature\Jobs;

use App\Constants\RoomStatus;
use App\Jobs\NotifyStuckCleaningRooms;
use App\Models\Hotel;

use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;

use App\Notifications\Housekeeping\StuckInCleaningNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

use Tests\TestCase;

class NotifyStuckCleaningRoomsTest extends TestCase
{
    use RefreshDatabase;

    public function test_notify_stuck_cleaning_rooms_sends_notification_for_stuck_rooms(): void
    {
        Notification::fake();

        $hotel = Hotel::factory()->create();
        $manager = User::factory()->create();
        $manager->hotels()->attach($hotel->id);

        // Seed roles in web guard
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'hotel_manager', 'guard_name' => 'web']);
        $manager->assignRole('hotel_manager');

        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);

        // Room stuck in cleaning (updated 3 hours ago)
        $stuckRoom = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'status' => RoomStatus::CLEANING,
            'updated_at' => now()->subHours(3),
        ]);

        // Room recently updated to cleaning (updated 10 minutes ago)
        $recentRoom = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'status' => RoomStatus::CLEANING,
            'updated_at' => now()->subMinutes(10),
        ]);

        // Dispatch job
        NotifyStuckCleaningRooms::dispatchSync();

        Notification::assertSentTo($manager, StuckInCleaningNotification::class, function ($notification) use ($stuckRoom) {
            return $notification->room->id === $stuckRoom->id;
        });

        // Room status should NOT be mutated by the job
        $this->assertEquals(RoomStatus::CLEANING, $stuckRoom->fresh()->status);
        $this->assertEquals(RoomStatus::CLEANING, $recentRoom->fresh()->status);
    }
}
