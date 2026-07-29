<?php

namespace Database\Factories;

use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    protected $model = Room::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hotel_id' => Hotel::factory(),
            'room_type_id' => function (array $attributes) {
                return RoomType::factory()->create([
                    'hotel_id' => $attributes['hotel_id'],
                ])->id;
            },
            'room_number' => fake()->unique()->numerify('R###'),
            'floor' => fake()->numberBetween(1, 5),
            'status' => 'available',
        ];
    }

    /**
     * State: Available Room.
     */
    public function available(): static
    {
        return $this->state([
            'status' => 'available',
        ]);
    }

    /**
     * State: Occupied Room.
     */
    public function occupied(): static
    {
        return $this->state([
            'status' => 'occupied',
        ]);
    }

    /**
     * State: Reserved Room.
     */
    public function reserved(): static
    {
        return $this->state([
            'status' => 'reserved',
        ]);
    }

    /**
     * State: Room being cleaned.
     */
    public function cleaning(): static
    {
        return $this->state([
            'status' => 'cleaning',
        ]);
    }

    /**
     * State: Room under maintenance.
     */
    public function maintenance(): static
    {
        return $this->state([
            'status' => 'maintenance',
        ]);
    }
}
