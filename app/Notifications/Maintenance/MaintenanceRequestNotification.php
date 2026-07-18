<?php

namespace App\Notifications\Maintenance;

use App\Constants\NotificationTypes;
use App\Models\MaintenanceRequest;
use App\Notifications\BaseNotification;

class MaintenanceRequestNotification extends BaseNotification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        public MaintenanceRequest $request
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
            'New maintenance request logged for room "%s". Priority is "%s". Issue: "%s".',
            $this->request->room?->room_number ?? 'Room',
            $this->request->priority,
            $this->request->title
        );

        return [
            'type' => NotificationTypes::MAINTENANCE_CREATED,
            'title' => 'New Maintenance Request',
            'message' => $message,
            'maintenance_request' => [
                'id' => $this->request->id,
                'room_id' => $this->request->room_id,
                'room_number' => $this->request->room?->room_number,
                'priority' => $this->request->priority,
                'status' => $this->request->status,
            ],
        ];
    }
}
