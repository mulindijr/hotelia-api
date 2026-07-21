<?php

namespace App\Services\Housekeeping;

use App\Constants\RoomStatus;
use App\Events\Housekeeping\HousekeepingTaskCreated;
use App\Events\Housekeeping\HousekeepingTaskUpdated;
use App\Events\Housekeeping\HousekeepingTaskDeleted;
use App\Models\Hotel;
use App\Models\HousekeepingTask;
use App\Models\MaintenanceRequest;
use Illuminate\Support\Facades\DB;

class HousekeepingService
{
    /**
     * Create a new housekeeping task inside a DB transaction.
     */
    public function create(Hotel $hotel, array $data): HousekeepingTask
    {
        return DB::transaction(function () use ($data) {
            $data['status'] = $data['status'] ?? 'pending';

            $task = HousekeepingTask::create($data);

            event(new HousekeepingTaskCreated($task));

            return $task->load('room');
        });
    }

    /**
     * Update a housekeeping task inside a DB transaction.
     */
    public function update(HousekeepingTask $task, array $data): HousekeepingTask
    {
        return DB::transaction(function () use ($task, $data) {
            $isBecomingCompleted = isset($data['status']) && $data['status'] === 'completed' && $task->status !== 'completed';

            if ($isBecomingCompleted) {
                $data['completed_at'] = $data['completed_at'] ?? now();
            }

            $task->update($data);

            if ($isBecomingCompleted && $task->room) {
                $room = $task->room;

                // Check for active maintenance requests
                $hasActiveMaintenance = MaintenanceRequest::where('room_id', $room->id)
                    ->whereIn('status', ['open', 'in_progress'])
                    ->exists();

                if ($hasActiveMaintenance) {
                    $room->update(['status' => RoomStatus::MAINTENANCE]);
                } else {
                    // Check for remaining pending/in_progress housekeeping tasks
                    $hasPendingTasks = HousekeepingTask::where('room_id', $room->id)
                        ->where('id', '!=', $task->id)
                        ->whereIn('status', ['pending', 'in_progress'])
                        ->exists();

                    if (!$hasPendingTasks) {
                        $room->update(['status' => RoomStatus::AVAILABLE]);
                    }
                }
            }

            event(new HousekeepingTaskUpdated($task));

            return $task->fresh()->load('room');
        });
    }

    /**
     * Delete a housekeeping task inside a DB transaction.
     */
    public function delete(HousekeepingTask $task): bool
    {
        return DB::transaction(function () use ($task) {
            event(new HousekeepingTaskDeleted($task));

            return $task->delete();
        });
    }
}
