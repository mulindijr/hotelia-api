<?php

namespace Database\Factories;

use App\Models\Hotel;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomType>
 */
class RoomTypeFactory extends Factory
{
    protected $model = RoomType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hotel_id' => Hotel::factory(),
            'name' => 'Standard Room',
            'description' => 'A comfortable room featuring essential amenities and a single queen-sized bed.',
            'capacity' => 2,
            'beds' => 1,
            'base_price' => 3500.00,
        ];
    }

    /**
     * Deluxe Room State.
     */
    public function deluxe(): static
    {
        return $this->state([
            'name' => 'Deluxe Room',
            'description' => 'An upgraded room offering more space, scenic views, and one king-sized bed.',
            'capacity' => 2,
            'beds' => 1,
            'base_price' => 6000.00,
        ]);
    }

    /**
     * Suite State.
     */
    public function suite(): static
    {
        return $this->state([
            'name' => 'Executive Suite',
            'description' => 'A luxurious spacious suite featuring a separate living area, workspace, and premium services.',
            'capacity' => 4,
            'beds' => 2,
            'base_price' => 12000.00,
        ]);
    }
}
