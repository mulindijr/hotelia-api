<?php

namespace App\Notifications\Housekeeping;

use App\Constants\NotificationTypes;
use App\Models\HousekeepingTask;
use App\Notifications\BaseNotification;

class HousekeepingTaskNotification extends BaseNotification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        public HousekeepingTask $task
    ) {
        parent::__construct();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $message = sprintf(
            'You have been assigned housekeeping task for room "%s". Status is "%s".',
            $this->task->room?->room_number ?? 'Room',
            $this->task->status
        );

        return [
            'type' => NotificationTypes::HOUSEKEEPING_ASSIGNED,
            'title' => 'Housekeeping Task Assigned',
            'message' => $message,
            'task' => [
                'id' => $this->task->id,
                'room_id' => $this->task->room_id,
                'room_number' => $this->task->room?->room_number,
                'status' => $this->task->status,
            ],
        ];
    }
}
