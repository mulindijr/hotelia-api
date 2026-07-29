<?php

namespace App\Mail\Bookings;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Confirmation - '.$this->booking->booking_reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.bookings.confirmation',
            with: [
                'booking' => $this->booking,
                'guest' => $this->booking->guest,
                'hotel' => $this->booking->hotel,
            ],
        );
    }
}
