<?php

namespace Database\Factories;

use App\Models\MaintenanceRequest;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceRequest>
 */
class MaintenanceRequestFactory extends Factory
{
    protected $model = MaintenanceRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'reported_by' => User::factory(),
            'description' => fake()->randomElement([
                'Leaking bathroom faucet.',
                'Air conditioner not cooling properly.',
                'Flickering ceiling light.',
                'Broken TV remote.',
                'Door lock card reader not responding.',
            ]),
            'priority' => 'medium',
            'status' => 'open',
        ];
    }

    /**
     * Low priority state.
     */
    public function low(): static
    {
        return $this->state([
            'priority' => 'low',
        ]);
    }

    /**
     * Medium priority state.
     */
    public function medium(): static
    {
        return $this->state([
            'priority' => 'medium',
        ]);
    }

    /**
     * High priority state.
     */
    public function high(): static
    {
        return $this->state([
            'priority' => 'high',
        ]);
    }

    /**
     * Critical priority state.
     */
    public function critical(): static
    {
        return $this->state([
            'priority' => 'critical',
        ]);
    }

    /**
     * Open status state.
     */
    public function open(): static
    {
        return $this->state([
            'status' => 'open',
        ]);
    }

    /**
     * In-progress status state.
     */
    public function inProgress(): static
    {
        return $this->state([
            'status' => 'in_progress',
        ]);
    }

    /**
     * Resolved status state.
     */
    public function resolved(): static
    {
        return $this->state([
            'status' => 'resolved',
        ]);
    }
}
