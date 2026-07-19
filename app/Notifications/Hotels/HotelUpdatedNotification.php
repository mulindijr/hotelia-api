<?php

namespace App\Notifications\Hotels;

use App\Constants\NotificationTypes;
use App\Models\Hotel;
use App\Notifications\BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;

class HotelUpdatedNotification extends BaseNotification
{
    public function __construct(
        public Hotel $hotel
    ) {}

    /**
     * Delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Database payload.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            // Safe, centralized constant string
            'type' => NotificationTypes::HOTEL_UPDATED,

            'title' => 'Hotel Updated',

            'message' => sprintf(
                'Hotel "%s" has been updated.',
                $this->hotel->name
            ),

            'hotel' => [
                'id' => $this->hotel->id,
                'name' => $this->hotel->name,
                'slug' => $this->hotel->slug,
            ],
        ];
    }

    /**
     * Email representation.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Hotel Updated')
            ->greeting("Hello {$notifiable->first_name},")
            ->line("Hotel '{$this->hotel->name}' has been updated.")
            ->line('Log in to view the changes.');
    }

    /**
     * Queue tags.
     */
    public function tags(): array
    {
        return [
            'hotel',
            'hotel:' . $this->hotel->id,
            NotificationTypes::HOTEL_UPDATED,
        ];
    }
}
