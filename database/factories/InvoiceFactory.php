<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 4000, 20000);
        $taxAmount = round($subtotal * 0.16, 2);
        $totalAmount = $subtotal + $taxAmount;

        return [
            'invoice_number' => 'INV-'.fake()->unique()->bothify('#####??'),
            'booking_id' => Booking::factory(),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'status' => 'unpaid',
        ];
    }

    /**
     * Unpaid state.
     */
    public function unpaid(): static
    {
        return $this->state([
            'status' => 'unpaid',
        ]);
    }

    /**
     * Partial state.
     */
    public function partial(): static
    {
        return $this->state([
            'status' => 'partial',
        ]);
    }

    /**
     * Paid state.
     */
    public function paid(): static
    {
        return $this->state([
            'status' => 'paid',
        ]);
    }
}
