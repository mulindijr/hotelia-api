<?php

namespace App\Notifications\Housekeeping;

use App\Constants\NotificationTypes;
use App\Models\Room;
use App\Notifications\BaseNotification;

class StuckInCleaningNotification extends BaseNotification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Room $room,
        public int $minutesCleaning
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
            'Room "%s" has been in cleaning status for over %d minutes and requires review.',
            $this->room->room_number,
            $this->minutesCleaning
        );

        return [
            'type' => NotificationTypes::ROOM_STUCK_IN_CLEANING,
            'title' => 'Room Stuck in Cleaning',
            'message' => $message,
            'room' => [
                'id' => $this->room->id,
                'room_number' => $this->room->room_number,
                'hotel_id' => $this->room->hotel_id,
                'minutes_cleaning' => $this->minutesCleaning,
            ],
        ];
    }
}
