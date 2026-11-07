<?php

namespace App\Http\Resources\Api\V1\Billing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'amount' => (float) $this->amount,
            'payment_method' => $this->payment_method,
            'transaction_reference' => $this->transaction_reference,
            'status' => $this->status,
            'payment_date' => $this->created_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'booking' => $this->whenLoaded('booking', function () {
                return [
                    'id' => $this->booking->id,
                    'booking_reference' => $this->booking->booking_reference,
                    'hotel_id' => $this->booking->hotel_id,
                    'check_in_date' => $this->booking->check_in_date?->toDateString(),
                    'check_out_date' => $this->booking->check_out_date?->toDateString(),
                    'total_amount' => (float) $this->booking->total_amount,
                    'grand_total' => (float) ($this->booking->grand_total ?? $this->booking->total_amount),
                    'status' => $this->booking->status,
                    'guest' => $this->booking->guest ? [
                        'id' => $this->booking->guest->id,
                        'first_name' => $this->booking->guest->first_name,
                        'last_name' => $this->booking->guest->last_name,
                        'email' => $this->booking->guest->email,
                        'phone' => $this->booking->guest->phone,
                    ] : null,
                ];
            }),
        ];
    }
}
