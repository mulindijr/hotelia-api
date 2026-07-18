<?php

namespace App\Notifications\Bookings;

use App\Constants\NotificationTypes;
use App\Models\Booking;
use App\Notifications\BaseNotification;

class BookingNotification extends BaseNotification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Booking $booking,
        public string $action
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
        $type = match ($this->action) {
            'created' => NotificationTypes::BOOKING_CREATED,
            'cancelled' => NotificationTypes::BOOKING_CANCELLED,
            'checked_in' => NotificationTypes::CHECK_IN,
            'checked_out' => NotificationTypes::CHECK_OUT,
            default => 'booking_notification',
        };

        $title = match ($this->action) {
            'created' => 'New Booking Created',
            'cancelled' => 'Booking Cancelled',
            'checked_in' => 'Guest Checked In',
            'checked_out' => 'Guest Checked Out',
            default => 'Booking Update',
        };

        $message = sprintf(
            'Booking "%s" for guest %s has been %s.',
            $this->booking->booking_reference,
            $this->booking->guest?->full_name ?? 'Guest',
            str_replace('_', ' ', $this->action)
        );

        return [
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'booking' => [
                'id' => $this->booking->id,
                'booking_reference' => $this->booking->booking_reference,
                'hotel_id' => $this->booking->hotel_id,
                'total_amount' => $this->booking->total_amount,
            ],
        ];
    }
}
