<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = fake()->dateTimeBetween('now', '+30 days');
        $checkOut = (clone $checkIn)->modify('+'.fake()->numberBetween(1, 5).' days');

        return [
            'booking_reference' => 'BK-'.fake()->unique()->bothify('#####??'),
            'hotel_id' => Hotel::factory(),
            'guest_id' => Guest::factory(),
            'check_in_date' => $checkIn->format('Y-m-d'),
            'check_out_date' => $checkOut->format('Y-m-d'),
            'adults' => fake()->numberBetween(1, 2),
            'children' => fake()->numberBetween(0, 2),
            'total_amount' => fake()->randomFloat(2, 5000, 30000),
            'status' => 'pending',
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Pending state.
     */
    public function pending(): static
    {
        return $this->state([
            'status' => 'pending',
        ]);
    }

    /**
     * Confirmed state.
     */
    public function confirmed(): static
    {
        return $this->state([
            'status' => 'confirmed',
        ]);
    }

    /**
     * Checked In state.
     */
    public function checkedIn(): static
    {
        $checkIn = fake()->dateTimeBetween('-3 days', 'now');
        $checkOut = (clone $checkIn)->modify('+'.fake()->numberBetween(1, 5).' days');

        return $this->state([
            'status' => 'checked_in',
            'check_in_date' => $checkIn->format('Y-m-d'),
            'check_out_date' => $checkOut->format('Y-m-d'),
        ]);
    }

    /**
     * Checked Out state.
     */
    public function checkedOut(): static
    {
        $checkIn = fake()->dateTimeBetween('-10 days', '-5 days');
        $checkOut = (clone $checkIn)->modify('+'.fake()->numberBetween(1, 5).' days');

        return $this->state([
            'status' => 'checked_out',
            'check_in_date' => $checkIn->format('Y-m-d'),
            'check_out_date' => $checkOut->format('Y-m-d'),
        ]);
    }

    /**
     * Cancelled state.
     */
    public function cancelled(): static
    {
        return $this->state([
            'status' => 'cancelled',
        ]);
    }
}
