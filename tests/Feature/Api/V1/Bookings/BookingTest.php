<?php

namespace Tests\Feature\Api\V1\Bookings;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Service;
use Tests\ApiTestCase;
use Tests\Traits\InteractsWithHotels;

class BookingTest extends ApiTestCase
{
    use InteractsWithHotels;

    /**
     * Test listing hotel bookings.
     */
    public function test_user_can_view_bookings_list(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        Booking::factory()->count(2)->create(['hotel_id' => $hotel->id]);

        $response = $this->getJson(route('bookings.index', $hotel));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    /**
     * Test security scoping.
     */
    public function test_user_cannot_view_unassigned_hotel_bookings(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = Hotel::factory()->create(); // Unassigned hotel

        $response = $this->getJson(route('bookings.index', $hotel));

        $response->assertStatus(403);
    }

    /**
     * Test creating a booking.
     */
    public function test_user_can_create_booking(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $guest = Guest::factory()->create(['hotel_id' => $hotel->id]);
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id, 'base_price' => 100.00]);
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
        ]);
        $service = Service::factory()->create(['hotel_id' => $hotel->id, 'price' => 25.00]);

        $payload = [
            'guest_id' => $guest->id,
            'check_in_date' => now()->addDays(2)->toDateString(),
            'check_out_date' => now()->addDays(4)->toDateString(), // 2 nights
            'adults' => 2,
            'rooms' => [$room->id],
            'services' => [
                ['id' => $service->id, 'quantity' => 2], // 2 * 25 = 50
            ],
            'notes' => 'Looking forward to stay.',
        ];

        $response = $this->postJson(route('bookings.store', $hotel), $payload);

        // Expected amount: (100 * 2 nights) + (25 * 2 qty) = 250
        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_amount', 250.00)
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('bookings', [
            'guest_id' => $guest->id,
            'total_amount' => 250.00,
        ]);
    }

    /**
     * Test validation prevents overlapping room bookings.
     */
    public function test_validation_prevents_overlapping_booking(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $guest = Guest::factory()->create(['hotel_id' => $hotel->id]);
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
        ]);

        // Create an existing booking for days 2 to 5
        $existingBooking = Booking::factory()->create([
            'hotel_id' => $hotel->id,
            'check_in_date' => now()->addDays(2)->toDateString(),
            'check_out_date' => now()->addDays(5)->toDateString(),
            'status' => 'confirmed',
        ]);
        $existingBooking->rooms()->attach($room->id, ['price_per_night' => 100.00]);

        // Attempt a new booking overlapping: days 4 to 6
        $payload = [
            'guest_id' => $guest->id,
            'check_in_date' => now()->addDays(4)->toDateString(),
            'check_out_date' => now()->addDays(6)->toDateString(),
            'rooms' => [$room->id],
        ];

        $response = $this->postJson(route('bookings.store', $hotel), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rooms']);
    }

    /**
     * Test checking in updates rooms to occupied.
     */
    public function test_receptionist_can_check_in_booking(): void
    {
        $user = $this->actingAsRole('receptionist');
        $hotel = $this->createHotelForUser($user);

        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        $booking = Booking::factory()->create([
            'hotel_id' => $hotel->id,
            'status' => 'confirmed',
        ]);
        $booking->rooms()->attach($room->id, ['price_per_night' => 100.00]);

        $response = $this->postJson(route('bookings.check-in', [$hotel, $booking]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'checked_in');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'checked_in',
        ]);

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'status' => 'occupied',
        ]);
    }

    /**
     * Test checking out updates rooms to cleaning.
     */
    public function test_receptionist_can_check_out_booking(): void
    {
        $user = $this->actingAsRole('receptionist');
        $hotel = $this->createHotelForUser($user);

        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'status' => 'occupied',
        ]);

        $booking = Booking::factory()->create([
            'hotel_id' => $hotel->id,
            'status' => 'checked_in',
        ]);
        $booking->rooms()->attach($room->id, ['price_per_night' => 100.00]);

        $response = $this->postJson(route('bookings.check-out', [$hotel, $booking]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'checked_out');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'checked_out',
        ]);

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'status' => 'cleaning',
        ]);
    }
}
