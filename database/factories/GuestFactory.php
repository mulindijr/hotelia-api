<?php

namespace Database\Factories;

use App\Models\Guest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Guest>
 */
class GuestFactory extends Factory
{
    protected $model = Guest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'nationality' => 'Kenyan',
            'passport_number' => null,
            'national_id' => fake()->unique()->numerify('########'),
        ];
    }

    /**
     * Domestic Guest State.
     */
    public function domestic(): static
    {
        return $this->state([
            'nationality' => 'Kenyan',
            'passport_number' => null,
            'national_id' => fake()->unique()->numerify('########'),
        ]);
    }

    /**
     * International Guest State.
     */
    public function international(): static
    {
        return $this->state([
            'nationality' => fake()->country(),
            'passport_number' => fake()->unique()->bothify('??#######'),
            'national_id' => null,
        ]);
    }
}
