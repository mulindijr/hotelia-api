<?php

namespace App\Services\Housekeeping;

use App\Events\Housekeeping\HousekeepingTaskCreated;
use App\Events\Housekeeping\HousekeepingTaskUpdated;
use App\Events\Housekeeping\HousekeepingTaskDeleted;
use App\Models\Hotel;
use App\Models\HousekeepingTask;

class HousekeepingService
{
    /**
     * Create a new housekeeping task.
     */
    public function create(Hotel $hotel, array $data): HousekeepingTask
    {
        $data['status'] = $data['status'] ?? 'pending';

        $task = HousekeepingTask::create($data);

        event(new HousekeepingTaskCreated($task));

        return $task->load('room');
    }

    /**
     * Update a housekeeping task.
     */
    public function update(HousekeepingTask $task, array $data): HousekeepingTask
    {
        // If status changes to completed, set completed_at to now
        if (isset($data['status']) && $data['status'] === 'completed' && $task->status !== 'completed') {
            $data['completed_at'] = $data['completed_at'] ?? now();
        }

        $task->update($data);

        event(new HousekeepingTaskUpdated($task));

        return $task->fresh()->load('room');
    }

    /**
     * Delete a housekeeping task.
     */
    public function delete(HousekeepingTask $task): bool
    {
        event(new HousekeepingTaskDeleted($task));

        return $task->delete();
    }
}
