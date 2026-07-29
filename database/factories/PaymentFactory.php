<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'amount' => fake()->randomFloat(2, 2000, 15000),
            'payment_method' => fake()->randomElement(['cash', 'card', 'mpesa', 'bank_transfer']),
            'transaction_reference' => null,
            'status' => 'pending',
        ];
    }

    /**
     * Pending payment state.
     */
    public function pending(): static
    {
        return $this->state([
            'status' => 'pending',
        ]);
    }

    /**
     * Completed payment state.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'transaction_reference' => 'TXN-'.fake()->unique()->bothify('#########??'),
        ]);
    }

    /**
     * Failed payment state.
     */
    public function failed(): static
    {
        return $this->state([
            'status' => 'failed',
        ]);
    }

    /**
     * Refunded payment state.
     */
    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'refunded',
            'transaction_reference' => 'REF-'.fake()->unique()->bothify('#########??'),
        ]);
    }
}
