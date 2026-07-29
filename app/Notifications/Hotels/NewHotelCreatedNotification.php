<?php

namespace App\Notifications\Hotels;

use App\Constants\NotificationTypes;
use App\Models\Hotel;
use App\Notifications\BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;

class NewHotelCreatedNotification extends BaseNotification
{
    public function __construct(
        protected Hotel $hotel
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [

            'type' => NotificationTypes::HOTEL_CREATED,

            'title' => 'New Hotel Created',

            'message' => "Hotel '{$this->hotel->name}' has been created.",

            'hotel' => [

                'id' => $this->hotel->id,

                'name' => $this->hotel->name,

                'slug' => $this->hotel->slug,
            ],
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)

            ->subject('New Hotel Created')

            ->greeting("Hello {$notifiable->first_name},")

            ->line("Hotel '{$this->hotel->name}' has been created.")

            ->line('Log in to view the hotel.');

        // Avoid embedding SPA URLs here. Let the frontend
        // determine navigation from the notification payload.
    }

    public function tags(): array
    {
        return [
            'hotel',
            'hotel:'.$this->hotel->id,
            NotificationTypes::HOTEL_CREATED,
        ];
    }
}
