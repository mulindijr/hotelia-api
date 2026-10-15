<?php

namespace App\Http\Resources\Api\V1\Bookings;

use App\Http\Resources\Api\V1\Guests\GuestResource;
use App\Http\Resources\Api\V1\Rooms\RoomResource;
use App\Http\Resources\Api\V1\Services\ServiceResource;
use App\Http\Resources\Api\V1\Billing\PaymentResource;
use App\Http\Resources\Api\V1\Billing\InvoiceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $invoice = $this->relationLoaded('invoice') ? $this->invoice : null;

        if ($invoice === null && $this->relationLoaded('invoices') && $this->invoices->isNotEmpty()) {
            $invoice = $this->invoices->first();
        }

        $taxRate = $invoice ? (float) ($invoice->tax_rate ?? 0) : null;
        $taxAmount = $invoice ? (float) $invoice->tax_amount : null;
        $grandTotal = $invoice ? (float) $invoice->total_amount : null;

        if ($taxAmount === null) {
            $settings = $this->hotel?->settings;
            $taxRate = (float) ($settings?->tax_rate ?? 0);
            $taxAmount = round((float) $this->total_amount * ($taxRate / 100), 2);
            $grandTotal = round((float) $this->total_amount + $taxAmount, 2);
        }

        return [
            'id' => $this->id,
            'booking_reference' => $this->booking_reference,
            'hotel_id' => $this->hotel_id,
            'guest_id' => $this->guest_id,
            'check_in_date' => $this->check_in_date?->toDateString(),
            'check_out_date' => $this->check_out_date?->toDateString(),
            'adults' => (int) $this->adults,
            'children' => (int) $this->children,
            'subtotal' => (float) $this->total_amount,
            'total_amount' => (float) $this->total_amount,
            'tax_rate' => (float) $taxRate,
            'tax_amount' => (float) $taxAmount,
            'grand_total' => (float) $grandTotal,
            'status' => $this->status,
            'notes' => $this->notes,
            'actual_check_in_at' => $this->actual_check_in_at?->toISOString(),
            'actual_check_out_at' => $this->actual_check_out_at?->toISOString(),
            'is_overbooked' => (bool) $this->is_overbooked,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'guest' => new GuestResource($this->whenLoaded('guest')),
            'rooms' => RoomResource::collection($this->whenLoaded('rooms')),
            'services' => ServiceResource::collection($this->whenLoaded('services')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'invoice' => new InvoiceResource($this->whenLoaded('invoice', fn() => $invoice)),
        ];
    }
}
