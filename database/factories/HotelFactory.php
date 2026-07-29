<?php

namespace Database\Factories;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Hotel>
 */
class HotelFactory extends Factory
{
    protected $model = Hotel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company().' Resort';

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'country' => fake()->country(),
            'city' => fake()->city(),
            'address' => fake()->streetAddress(),
            'description' => fake()->paragraph(),
            'logo' => null,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the hotel is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the hotel is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the hotel is a luxury establishment.
     */
    public function luxury(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'The Grand '.$attributes['name'],
            'slug' => Str::slug('The Grand '.$attributes['name']),
            'description' => 'A luxury 5-star resort offering premium services, fine dining, and full spa facilities.',
        ]);
    }

    /**
     * Indicate that the hotel is a budget establishment.
     */
    public function budget(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $attributes['name'].' Inn',
            'slug' => Str::slug($attributes['name'].' Inn'),
            'description' => 'Affordable and cozy accommodations offering essential amenities for cost-conscious travelers.',
        ]);
    }
}
