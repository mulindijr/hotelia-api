<?php

namespace App\Mail\Billing;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoicePaidMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice Paid - Thank You - '.$this->invoice->invoice_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.billing.paid',
            with: [
                'invoice' => $this->invoice,
                'booking' => $this->invoice->booking,
                'guest' => $this->invoice->booking?->guest,
                'hotel' => $this->invoice->booking?->hotel,
            ],
        );
    }
}
