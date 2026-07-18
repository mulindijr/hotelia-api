<?php

namespace Database\Factories;

use App\Models\Amenity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Amenity>
 */
class AmenityFactory extends Factory
{
    protected $model = Amenity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
        ];
    }

    /**
     * Predefined state: Free Wi-Fi.
     */
    public function wifi(): static
    {
        return $this->state([
            'name' => 'Free Wi-Fi',
            'description' => 'Complimentary high-speed wireless internet access.',
        ]);
    }

    /**
     * Predefined state: Air Conditioning.
     */
    public function ac(): static
    {
        return $this->state([
            'name' => 'Air Conditioning',
            'description' => 'In-room climate control with heating and cooling options.',
        ]);
    }

    /**
     * Predefined state: Swimming Pool.
     */
    public function pool(): static
    {
        return $this->state([
            'name' => 'Swimming Pool',
            'description' => 'Access to the hotel outdoor heated swimming pool.',
        ]);
    }

    /**
     * Predefined state: Gym.
     */
    public function gym(): static
    {
        return $this->state([
            'name' => 'Fitness Center',
            'description' => '24/7 access to state-of-the-art gym equipment.',
        ]);
    }

    /**
     * Predefined state: Minibar.
     */
    public function minibar(): static
    {
        return $this->state([
            'name' => 'Minibar',
            'description' => 'Fully stocked minibar with selection of beverages and snacks.',
        ]);
    }
}
