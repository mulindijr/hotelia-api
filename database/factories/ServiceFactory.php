<?php

namespace Database\Factories;

use App\Models\Hotel;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hotel_id' => Hotel::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 500, 5000),
            'is_active' => true,
        ];
    }

    /**
     * Active state.
     */
    public function active(): static
    {
        return $this->state([
            'is_active' => true,
        ]);
    }

    /**
     * Inactive state.
     */
    public function inactive(): static
    {
        return $this->state([
            'is_active' => false,
        ]);
    }

    /**
     * Spa service state.
     */
    public function spa(): static
    {
        return $this->state([
            'name' => 'Spa Treatment',
            'description' => 'A relaxing 60-minute therapeutic body massage.',
            'price' => 3500.00,
        ]);
    }

    /**
     * Laundry service state.
     */
    public function laundry(): static
    {
        return $this->state([
            'name' => 'Laundry Service',
            'description' => 'Professional washing, drying, and ironing service per load.',
            'price' => 1200.00,
        ]);
    }

    /**
     * Airport Shuttle service state.
     */
    public function airportShuttle(): static
    {
        return $this->state([
            'name' => 'Airport Shuttle Transfer',
            'description' => 'One-way comfortable private transfer to or from the airport.',
            'price' => 2500.00,
        ]);
    }
}
