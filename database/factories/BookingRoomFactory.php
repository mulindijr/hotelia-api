<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingRoom>
 */
class BookingRoomFactory extends Factory
{
    protected $model = BookingRoom::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'room_id' => function (array $attributes) {
                // Ensure the assigned room belongs to the same hotel as the booking
                $booking = Booking::find($attributes['booking_id']);
                $hotelId = $booking ? $booking->hotel_id : Hotel::factory()->create()->id;

                return Room::factory()->create([
                    'hotel_id' => $hotelId,
                ])->id;
            },
            'price_per_night' => function (array $attributes) {
                $room = Room::find($attributes['room_id']);

                return $room && $room->roomType ? $room->roomType->base_price : 3500.00;
            },
        ];
    }
}
