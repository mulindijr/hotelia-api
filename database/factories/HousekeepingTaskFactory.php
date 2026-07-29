<?php

namespace Database\Factories;

use App\Models\HousekeepingTask;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HousekeepingTask>
 */
class HousekeepingTaskFactory extends Factory
{
    protected $model = HousekeepingTask::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'assigned_to' => User::factory(),
            'status' => 'pending',
            'scheduled_at' => fake()->dateTimeBetween('now', '+2 days'),
            'completed_at' => null,
        ];
    }

    /**
     * Pending state.
     */
    public function pending(): static
    {
        return $this->state([
            'status' => 'pending',
            'completed_at' => null,
        ]);
    }

    /**
     * In-progress state.
     */
    public function inProgress(): static
    {
        return $this->state([
            'status' => 'in_progress',
            'completed_at' => null,
        ]);
    }

    /**
     * Completed state.
     */
    public function completed(): static
    {
        $scheduled = fake()->dateTimeBetween('-2 days', 'now');
        $completed = (clone $scheduled)->modify('+'.fake()->numberBetween(30, 90).' minutes');

        return $this->state([
            'status' => 'completed',
            'scheduled_at' => $scheduled,
            'completed_at' => $completed,
        ]);
    }
}
