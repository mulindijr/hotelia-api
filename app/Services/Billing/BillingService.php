<?php

namespace App\Services\Billing;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
     * Recompute and regenerate booking invoice inside a DB transaction.
     */
    public function regenerateInvoice(Booking $booking): Invoice
    {
        return DB::transaction(function () use ($booking) {
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

            // 3.5. Add early check-in and late check-out fees if applicable
            if ($booking->actual_check_in_at) {
                $actualCheckIn = Carbon::parse($booking->actual_check_in_at);
                $scheduledCheckInLimit = Carbon::parse($booking->check_in_date->toDateString() . ' ' . ($hotel->settings?->check_in_time ?? '14:00'));
                if ($actualCheckIn->lt($scheduledCheckInLimit)) {
                    $earlyFee = $hotel->settings?->early_checkin_fee ?? 0.00;
                    if ($earlyFee > 0) {
                        $subtotal += $earlyFee;
                        $itemsData[] = [
                            'description' => "Early Check-in Fee",
                            'quantity' => 1,
                            'unit_price' => $earlyFee,
                            'total_price' => $earlyFee,
                        ];
                    }
                }
            }

            if ($booking->actual_check_out_at) {
                $actualCheckOut = Carbon::parse($booking->actual_check_out_at);
                $scheduledCheckOutLimit = Carbon::parse($booking->check_out_date->toDateString() . ' ' . ($hotel->settings?->check_out_time ?? '11:00'))
                    ->addMinutes($hotel->settings?->default_checkout_grace_minutes ?? 0);
                if ($actualCheckOut->gt($scheduledCheckOutLimit)) {
                    $lateFee = $hotel->settings?->late_checkout_fee ?? 0.00;
                    if ($lateFee > 0) {
                        $subtotal += $lateFee;
                        $itemsData[] = [
                            'description' => "Late Check-out Fee",
                            'quantity' => 1,
                            'unit_price' => $lateFee,
                            'total_price' => $lateFee,
                        ];
                    }
                }
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
        });
    }

    /**
     * Record a settlement transaction for the booking.
     */
    public function logPayment(Booking $booking, array $data): Payment
    {
        return DB::transaction(function () use ($booking, $data) {
            $payment = $booking->payments()->create([
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'status' => $data['status'] ?? 'completed',
            ]);

            $this->recalculateInvoiceStatus($booking);

            return $payment;
        });
    }

    /**
     * Update transaction status.
     */
    public function updatePaymentStatus(Payment $payment, string $status): Payment
    {
        return DB::transaction(function () use ($payment, $status) {
            $payment->update(['status' => $status]);

            $this->recalculateInvoiceStatus($payment->booking);

            return $payment;
        });
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
