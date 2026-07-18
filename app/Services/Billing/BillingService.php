<?php

namespace App\Services\Billing;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Str;

class BillingService
{
    /**
     * Retrieve existing invoice or generate a new one.
     */
    public function getOrGenerateInvoice(Booking $booking): Invoice
    {
        $invoice = $booking->invoices()->first();

        if (!$invoice) {
            $invoice = $this->regenerateInvoice($booking);
        }

        return $invoice->load('items');
    }

    /**
     * Recompute and regenerate booking invoice.
     */
    public function regenerateInvoice(Booking $booking): Invoice
    {
        $hotel = $booking->hotel;
        $taxRate = $hotel->settings?->tax_rate ?? 0.00;

        // 1. Calculate nights stay
        $checkIn = Carbon::parse($booking->check_in_date);
        $checkOut = Carbon::parse($booking->check_out_date);
        $nights = max(1, $checkIn->diffInDays($checkOut));

        $subtotal = 0.00;
        $itemsData = [];

        // 2. Add rooms stays
        $bookingRooms = $booking->rooms()->get();
        foreach ($bookingRooms as $room) {
            $pricePerNight = $room->pivot->price_per_night;
            $totalPrice = $pricePerNight * $nights;
            $subtotal += $totalPrice;

            $itemsData[] = [
                'description' => "Room {$room->room_number} Nightly Stay ({$nights} nights)",
                'quantity' => 1,
                'unit_price' => $totalPrice,
                'total_price' => $totalPrice,
            ];
        }

        // 3. Add services
        $bookingServices = $booking->services()->get();
        foreach ($bookingServices as $service) {
            $price = $service->pivot->price;
            $qty = $service->pivot->quantity;
            $totalPrice = $price * $qty;
            $subtotal += $totalPrice;

            $itemsData[] = [
                'description' => "Service: {$service->name}",
                'quantity' => $qty,
                'unit_price' => $price,
                'total_price' => $totalPrice,
            ];
        }

        $taxAmount = $subtotal * ($taxRate / 100);
        $totalAmount = $subtotal + $taxAmount;

        // 4. Save Invoice
        $invoice = $booking->invoices()->first();

        if (!$invoice) {
            $prefix = $hotel->settings?->invoice_prefix ?? 'INV-';
            do {
                $invoiceNumber = $prefix . date('Ymd') . strtoupper(Str::random(4));
            } while (Invoice::where('invoice_number', $invoiceNumber)->exists());

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'booking_id' => $booking->id,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => 'unpaid',
            ]);
        } else {
            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
            ]);
        }

        // Sync items
        $invoice->items()->delete();
        foreach ($itemsData as $item) {
            $invoice->items()->create($item);
        }

        // Recalculate status
        $this->recalculateInvoiceStatus($booking);

        return $invoice->fresh('items');
    }

    /**
     * Record a settlement transaction for the booking.
     */
    public function logPayment(Booking $booking, array $data): Payment
    {
        $payment = $booking->payments()->create([
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'transaction_reference' => $data['transaction_reference'] ?? null,
            'status' => $data['status'] ?? 'completed',
        ]);

        $this->recalculateInvoiceStatus($booking);

        return $payment;
    }

    /**
     * Update transaction status.
     */
    public function updatePaymentStatus(Payment $payment, string $status): Payment
    {
        $payment->update(['status' => $status]);

        $this->recalculateInvoiceStatus($payment->booking);

        return $payment;
    }

    /**
     * Align invoice status to matched paid total.
     */
    public function recalculateInvoiceStatus(Booking $booking): void
    {
        $invoice = $booking->invoices()->first();
        if (!$invoice) {
            return;
        }

        $paidTotal = $booking->payments()
            ->where('status', 'completed')
            ->sum('amount');

        $status = 'unpaid';
        if ($paidTotal >= $invoice->total_amount) {
            $status = 'paid';
        } elseif ($paidTotal > 0) {
            $status = 'partial';
        }

        $invoice->update(['status' => $status]);
    }
}
