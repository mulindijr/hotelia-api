<?php

namespace App\Services\Pdf;

use App\Models\Invoice;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PdfService
{
    /**
     * Generate raw binary PDF output for an invoice.
     */
    public function generateInvoicePdfOutput(Invoice $invoice): string
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

        // Derive tax rate accurately from invoice, hotel settings, or calculate from subtotal & tax_amount
        $taxRate = (float) (
            $invoice->tax_rate 
            ?? $settings?->tax_rate 
            ?? ($invoice->subtotal > 0 ? round(($invoice->tax_amount / $invoice->subtotal) * 100, 2) : 0)
        );

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'hotel' => $hotel,
            'booking' => $booking,
            'guest' => $guest,
            'currency' => $currency,
            'totalPaid' => $totalPaid,
            'balanceDue' => $balanceDue,
            'taxRate' => $taxRate,
        ]);

        return $pdf->output();
    }

    /**
     * Render guest invoice as PDF response.
     */
    public function renderInvoicePdf(Invoice $invoice): Response
    {
        $filename = "invoice_{$invoice->invoice_number}.pdf";
        $output = $this->generateInvoicePdfOutput($invoice);

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($output),
        ]);
    }

    /**
     * Generate raw binary PDF output for a payment receipt.
     */
    public function generatePaymentReceiptPdfOutput(Payment $payment): string
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

        return $pdf->output();
    }

    /**
     * Render payment receipt as PDF response.
     */
    public function renderPaymentReceiptPdf(Payment $payment): Response
    {
        $filename = "receipt_#{$payment->id}.pdf";
        $output = $this->generatePaymentReceiptPdfOutput($payment);

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($output),
        ]);
    }
}
