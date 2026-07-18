<?php

namespace App\Listeners\Housekeeping;

use App\Notifications\Housekeeping\HousekeepingTaskNotification;

class SendHousekeepingTaskNotification
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $task = $event->task;
        $housekeeper = $task->assignedTo;

        if ($housekeeper) {
            $housekeeper->notify(new HousekeepingTaskNotification($task));
        }
    }
}
