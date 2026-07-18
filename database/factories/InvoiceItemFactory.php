<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'description' => fake()->words(3, true),
            'quantity' => fake()->numberBetween(1, 5),
            'unit_price' => fake()->randomFloat(2, 500, 5000),
            'total_price' => function (array $attributes) {
                return round($attributes['quantity'] * $attributes['unit_price'], 2);
            },
        ];
    }
}
