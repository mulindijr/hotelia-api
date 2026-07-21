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

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $hotel = Hotel::factory()->create();
        $manager = User::factory()->create();
        $manager->hotels()->attach($hotel->id);
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

    public function test_notify_stuck_cleaning_rooms_prevents_duplicate_notifications_within_cooldown(): void
    {
        Notification::fake();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $hotel = Hotel::factory()->create();
        $housekeeper = User::factory()->create();
        $housekeeper->hotels()->attach($hotel->id);
        $housekeeper->givePermissionTo(\App\Constants\Permissions::VIEW_HOUSEKEEPING);

        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);

        $stuckRoom = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'status' => RoomStatus::CLEANING,
            'updated_at' => now()->subHours(3),
        ]);

        // First run sends notification
        NotifyStuckCleaningRooms::dispatchSync();
        Notification::assertSentToTimes($housekeeper, StuckInCleaningNotification::class, 1);

        // Second run immediately after should NOT send duplicate notification due to cooldown
        NotifyStuckCleaningRooms::dispatchSync();
        Notification::assertSentToTimes($housekeeper, StuckInCleaningNotification::class, 1);
    }
}
