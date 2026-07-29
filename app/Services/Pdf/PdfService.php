<?php

namespace App\Services\Pdf;

use App\Models\Invoice;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PdfService
{
    /**
     * Render guest invoice as PDF response.
     */
    public function renderInvoicePdf(Invoice $invoice): Response
    {
        $invoice->loadMissing([
            'booking.hotel.settings',
            'booking.guest',
            'booking.rooms.roomType',
            'booking.payments',
            'items',
        ]);

        $booking = $invoice->booking;
        $hotel = $booking->hotel;
        $guest = $booking->guest;

        $settings = $hotel->settings;
        $currency = $settings->currency ?? 'USD';

        $totalPaid = $booking->payments
            ->where('status', 'completed')
            ->sum('amount');

        $balanceDue = max(0, (float) $invoice->total_amount - (float) $totalPaid);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'hotel' => $hotel,
            'booking' => $booking,
            'guest' => $guest,
            'currency' => $currency,
            'totalPaid' => $totalPaid,
            'balanceDue' => $balanceDue,
        ]);

        $filename = "invoice_{$invoice->invoice_number}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Render payment receipt as PDF response.
     */
    public function renderPaymentReceiptPdf(Payment $payment): Response
    {
        $payment->loadMissing([
            'booking.guest',
            'booking.hotel.settings',
        ]);

        $booking = $payment->booking;
        $hotel = $booking->hotel;
        $guest = $booking->guest;

        $settings = $hotel->settings;
        $currency = $settings->currency ?? 'USD';

        $pdf = Pdf::loadView('pdf.receipt', [
            'payment' => $payment,
            'booking' => $booking,
            'hotel' => $hotel,
            'guest' => $guest,
            'currency' => $currency,
        ]);

        $filename = "receipt_#{$payment->id}.pdf";

        return $pdf->download($filename);
    }
}
