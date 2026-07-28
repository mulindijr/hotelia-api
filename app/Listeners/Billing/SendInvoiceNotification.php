<?php

namespace App\Listeners\Billing;

use App\Events\Billing\InvoiceGenerated;
use App\Events\Billing\InvoicePaid;
use App\Mail\Billing\InvoiceGeneratedMail;
use App\Mail\Billing\InvoicePaidMail;
use Illuminate\Support\Facades\Mail;

class SendInvoiceNotification
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $invoice = $event->invoice;
        $booking = $invoice->booking;
        $guest = $booking?->guest;

        if (!$guest || !$guest->email) {
            return;
        }

        // Eager load details needed by templates
        $invoice->loadMissing(['items', 'booking.guest', 'booking.hotel.settings']);

        if ($event instanceof InvoiceGenerated) {
            Mail::to($guest->email)->send(new InvoiceGeneratedMail($invoice));
        } elseif ($event instanceof InvoicePaid) {
            Mail::to($guest->email)->send(new InvoicePaidMail($invoice));
        }
    }
}
