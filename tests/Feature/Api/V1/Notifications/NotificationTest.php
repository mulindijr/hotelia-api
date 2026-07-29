<?php

namespace Tests\Feature\Api\V1\Notifications;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\ApiTestCase;
use Tests\Traits\InteractsWithHotels;

class NotificationTest extends ApiTestCase
{
    use InteractsWithHotels;

    /**
     * Test user can view their notifications list.
     */
    public function test_user_can_view_notifications_list(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        // Add database notifications manually
        $user->notifications()->create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\Bookings\BookingNotification',
            'data' => [
                'type' => 'booking_created',
                'title' => 'New Booking Created',
                'message' => 'Booking BK-123 has been created.',
            ],
            'read_at' => null,
        ]);

        $response = $this->getJson(route('notifications.index'));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }

    /**
     * Test marking notification as read.
     */
    public function test_user_can_mark_notification_as_read(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $notificationId = Str::uuid()->toString();
        $user->notifications()->create([
            'id' => $notificationId,
            'type' => 'App\Notifications\Bookings\BookingNotification',
            'data' => [
                'type' => 'booking_created',
                'title' => 'New Booking Created',
                'message' => 'Booking BK-123 has been created.',
            ],
            'read_at' => null,
        ]);

        $response = $this->postJson(route('notifications.read', $notificationId));

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertNotNull($user->notifications()->find($notificationId)->read_at);
    }

    /**
     * Test marking all notifications as read.
     */
    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $user->notifications()->create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\Bookings\BookingNotification',
            'data' => ['type' => 'booking_created'],
            'read_at' => null,
        ]);
        $user->notifications()->create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\Bookings\BookingNotification',
            'data' => ['type' => 'booking_created'],
            'read_at' => null,
        ]);

        $response = $this->postJson(route('notifications.read-all'));

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertEquals(0, $user->unreadNotifications()->count());
    }

    /**
     * Test booking creation triggers database notifications to staff.
     */
    public function test_booking_creation_dispatches_notification_to_staff(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $guest = Guest::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
        ]);

        $payload = [
            'guest_id' => $guest->id,
            'check_in_date' => now()->addDays(2)->toDateString(),
            'check_out_date' => now()->addDays(4)->toDateString(),
            'rooms' => [$room->id],
        ];

        // Trigger store booking endpoint
        $this->postJson(route('bookings.store', $hotel), $payload)->assertCreated();

        // Hotel manager user should receive a notification
        $this->assertEquals(1, $user->unreadNotifications()->count());
    }

    /**
     * Test housekeeping assignments alert the assigned housekeeper.
     */
    public function test_housekeeping_task_creation_dispatches_notification_to_housekeeper(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $housekeeper = User::factory()->create();
        $housekeeper->assignRole('housekeeper');
        $hotel->users()->attach($housekeeper->id);

        $room = Room::factory()->create(['hotel_id' => $hotel->id]);

        $payload = [
            'room_id' => $room->id,
            'assigned_to' => $housekeeper->id,
            'status' => 'pending',
            'scheduled_at' => now()->addDay()->toDateTimeString(),
        ];

        // Trigger store housekeeping task
        $this->postJson(route('housekeeping.store', $hotel), $payload)->assertCreated();

        // The housekeeper should have a notification in their box
        $this->assertEquals(1, $housekeeper->unreadNotifications()->count());
    }

    /**
     * Test maintenance request log notifies hotel managers.
     */
    public function test_maintenance_request_creation_dispatches_notification_to_manager(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $room = Room::factory()->create(['hotel_id' => $hotel->id]);

        $payload = [
            'room_id' => $room->id,
            'description' => 'AC Broken',
            'priority' => 'high',
            'status' => 'open',
        ];

        // Trigger store maintenance request
        $this->postJson(route('maintenance.store', $hotel), $payload)->assertCreated();

        // The manager should receive a notification
        $this->assertEquals(1, $user->unreadNotifications()->count());
    }
}
