<?php

namespace App\Mail\Billing;

use App\Models\Invoice;
use App\Services\Pdf\PdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceGeneratedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice Issued - '.$this->invoice->invoice_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.billing.generated',
            with: [
                'invoice' => $this->invoice,
                'booking' => $this->invoice->booking,
                'guest' => $this->invoice->booking?->guest,
                'hotel' => $this->invoice->booking?->hotel,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $pdfOutput = app(PdfService::class)->generateInvoicePdfOutput($this->invoice);
        $filename = "invoice_{$this->invoice->invoice_number}.pdf";

        return [
            Attachment::fromData(fn () => $pdfOutput, $filename)
                ->withMime('application/pdf'),
        ];
    }
}
