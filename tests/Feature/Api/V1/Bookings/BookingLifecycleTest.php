<?php

namespace Tests\Feature\Api\V1\Bookings;

use App\Constants\BookingStatus;
use App\Constants\RoomStatus;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\HotelSetting;
use App\Models\Room;
use App\Models\RoomType;
use Carbon\Carbon;
use Tests\ApiTestCase;
use Tests\Traits\InteractsWithHotels;

class BookingLifecycleTest extends ApiTestCase
{
    use InteractsWithHotels;

    public function test_room_status_becomes_reserved_on_booking_creation(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $guest = Guest::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'status' => RoomStatus::AVAILABLE,
        ]);

        $response = $this->postJson(route('bookings.store', $hotel), [
            'guest_id' => $guest->id,
            'check_in_date' => now()->addDays(2)->toDateString(),
            'check_out_date' => now()->addDays(4)->toDateString(),
            'rooms' => [$room->id],
            'status' => BookingStatus::PENDING,
        ]);

        $response->assertCreated();
        $room->refresh();
        $this->assertEquals(RoomStatus::RESERVED, $room->status);
    }

    public function test_booking_lifecycle_transitions(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);
        $guest = Guest::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
        ]);

        $booking = Booking::factory()->create([
            'hotel_id' => $hotel->id,
            'guest_id' => $guest->id,
            'status' => BookingStatus::PENDING,
            'check_in_date' => now()->addDays(2)->toDateString(),
            'check_out_date' => now()->addDays(4)->toDateString(),
        ]);
        $booking->rooms()->attach($room->id, ['price_per_night' => 100.00]);

        // Check in booking
        $response = $this->postJson(route('bookings.check-in', [$hotel, $booking]));
        $response->assertOk();
        $booking->refresh();
        $room->refresh();
        $this->assertEquals(BookingStatus::CHECKED_IN, $booking->status);
        $this->assertEquals(RoomStatus::OCCUPIED, $room->status);
        $this->assertNotNull($booking->actual_check_in_at);

        // Try to cancel checked_in booking -> should fail (HTTP 422)
        $response = $this->postJson(route('bookings.cancel', [$hotel, $booking]));
        $response->assertStatus(422);

        // Check out booking
        $response = $this->postJson(route('bookings.check-out', [$hotel, $booking]));
        $response->assertOk();
        $booking->refresh();
        $room->refresh();
        $this->assertEquals(BookingStatus::CHECKED_OUT, $booking->status);
        $this->assertEquals(RoomStatus::CLEANING, $room->status);
        $this->assertNotNull($booking->actual_check_out_at);

        // Try to mark checked_out booking as no-show -> should fail (HTTP 422)
        $response = $this->postJson(route('bookings.no-show', [$hotel, $booking]));
        $response->assertStatus(422);
    }

    public function test_no_show_transitions_status_and_releases_room(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);
        $guest = Guest::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'status' => RoomStatus::RESERVED,
        ]);

        $booking = Booking::factory()->create([
            'hotel_id' => $hotel->id,
            'guest_id' => $guest->id,
            'status' => BookingStatus::CONFIRMED,
            'check_in_date' => now()->addDays(2)->toDateString(),
            'check_out_date' => now()->addDays(4)->toDateString(),
        ]);
        $booking->rooms()->attach($room->id, ['price_per_night' => 100.00]);

        $response = $this->postJson(route('bookings.no-show', [$hotel, $booking]));
        $response->assertOk();
        
        $booking->refresh();
        $room->refresh();
        $this->assertEquals(BookingStatus::NO_SHOW, $booking->status);
        $this->assertEquals(RoomStatus::AVAILABLE, $room->status);
    }

    public function test_overbooking_setting_enforcement(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);
        
        // Disable overbooking
        $hotel->settings()->update(['allow_overbooking' => false]);

        $guest1 = Guest::factory()->create();
        $guest2 = Guest::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
        ]);

        // Create booking 1
        $this->postJson(route('bookings.store', $hotel), [
            'guest_id' => $guest1->id,
            'check_in_date' => '2026-08-01',
            'check_out_date' => '2026-08-05',
            'rooms' => [$room->id],
            'status' => BookingStatus::CONFIRMED,
        ])->assertCreated();

        // Create booking 2 overlapping -> should fail because allow_overbooking is false
        $this->postJson(route('bookings.store', $hotel), [
            'guest_id' => $guest2->id,
            'check_in_date' => '2026-08-03',
            'check_out_date' => '2026-08-07',
            'rooms' => [$room->id],
            'status' => BookingStatus::CONFIRMED,
        ])->assertStatus(422);

        // Enable overbooking
        $hotel->settings()->update(['allow_overbooking' => true]);

        // Create booking 2 overlapping again -> should succeed and flag is_overbooked as true
        $response = $this->postJson(route('bookings.store', $hotel), [
            'guest_id' => $guest2->id,
            'check_in_date' => '2026-08-03',
            'check_out_date' => '2026-08-07',
            'rooms' => [$room->id],
            'status' => BookingStatus::CONFIRMED,
        ]);

        $response->assertCreated();
        $this->assertTrue($response->json('data.is_overbooked'));
    }

    public function test_cancellation_window_enforcement(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);
        
        $hotel->settings()->update([
            'booking_cancellation_hours' => 24,
            'check_in_time' => '14:00',
        ]);

        $guest = Guest::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
        ]);

        // Booking checking in today (less than 24 hours window since now is e.g. 18:00 or checkin is 14:00)
        $booking = Booking::factory()->create([
            'hotel_id' => $hotel->id,
            'guest_id' => $guest->id,
            'status' => BookingStatus::CONFIRMED,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
        ]);
        $booking->rooms()->attach($room->id, ['price_per_night' => 100.00]);

        // Try to cancel -> should fail (HTTP 422)
        $response = $this->postJson(route('bookings.cancel', [$hotel, $booking]));
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);

        // Booking checking in 3 days from now
        $booking2 = Booking::factory()->create([
            'hotel_id' => $hotel->id,
            'guest_id' => $guest->id,
            'status' => BookingStatus::CONFIRMED,
            'check_in_date' => now()->addDays(3)->toDateString(),
            'check_out_date' => now()->addDays(5)->toDateString(),
        ]);
        $booking2->rooms()->attach($room->id, ['price_per_night' => 100.00]);

        // Try to cancel -> should succeed
        $response2 = $this->postJson(route('bookings.cancel', [$hotel, $booking2]));
        $response2->assertOk();
        $this->assertEquals(BookingStatus::CANCELLED, $booking2->refresh()->status);
    }
}
