<?php

namespace Tests\Feature\Api\V1\Rooms;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Models\RoomType;
use Tests\ApiTestCase;
use Tests\Traits\InteractsWithHotels;

class AvailabilityTest extends ApiTestCase
{
    use InteractsWithHotels;

    public function test_user_can_query_availability_matrix(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id, 'base_price' => 150.00]);
        Room::factory()->count(5)->create(['hotel_id' => $hotel->id, 'room_type_id' => $roomType->id]);

        $response = $this->getJson("/api/v1/hotels/{$hotel->id}/availability?start_date=2026-08-01&end_date=2026-08-05");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_days', 4)
            ->assertJsonPath('data.matrix.0.room_types.0.total_rooms', 5)
            ->assertJsonPath('data.matrix.0.room_types.0.available_rooms', 5)
            ->assertJsonPath('data.matrix.0.room_types.0.status', 'available');
    }

    public function test_availability_matrix_reflects_booked_rooms(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id, 'base_price' => 100.00]);
        $rooms = Room::factory()->count(2)->create(['hotel_id' => $hotel->id, 'room_type_id' => $roomType->id]);

        $guest = Guest::factory()->create();

        $booking = Booking::create([
            'booking_reference' => 'BK-TEST123',
            'hotel_id' => $hotel->id,
            'guest_id' => $guest->id,
            'check_in_date' => '2026-08-01',
            'check_out_date' => '2026-08-03',
            'total_amount' => 200.00,
            'status' => 'confirmed',
        ]);
        $booking->rooms()->attach($rooms->first()->id, ['price_per_night' => 100.00]);

        $response = $this->getJson("/api/v1/hotels/{$hotel->id}/availability?start_date=2026-08-01&end_date=2026-08-03");

        $response->assertOk()
            ->assertJsonPath('data.matrix.0.room_types.0.total_rooms', 2)
            ->assertJsonPath('data.matrix.0.room_types.0.booked_rooms', 1)
            ->assertJsonPath('data.matrix.0.room_types.0.available_rooms', 1);
    }
}
