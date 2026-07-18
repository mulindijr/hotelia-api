<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingService;
use App\Models\Hotel;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingService>
 */
class BookingServiceFactory extends Factory
{
    protected $model = BookingService::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'service_id' => function (array $attributes) {
                // Ensure the service belongs to the same hotel as the booking
                $booking = Booking::find($attributes['booking_id']);
                $hotelId = $booking ? $booking->hotel_id : Hotel::factory()->create()->id;

                return Service::factory()->create([
                    'hotel_id' => $hotelId
                ])->id;
            },
            'quantity' => fake()->numberBetween(1, 3),
            'price' => function (array $attributes) {
                $service = Service::find($attributes['service_id']);
                return $service ? $service->price : 1000.00;
            },
        ];
    }
}
