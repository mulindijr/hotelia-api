<?php

namespace App\Mail\Bookings;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingNoShowMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking No-Show Notice - ' . $this->booking->booking_reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.bookings.noshow',
            with: [
                'booking' => $this->booking,
                'guest' => $this->booking->guest,
                'hotel' => $this->booking->hotel,
            ],
        );
    }
}
