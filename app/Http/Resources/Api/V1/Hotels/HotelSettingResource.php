<?php

namespace App\Http\Resources\Api\V1\Hotels;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'hotel_id' => $this->hotel_id,
            'currency' => $this->currency,
            'timezone' => $this->timezone,
            'language' => $this->language,
            'check_in_time' => $this->check_in_time,
            'check_out_time' => $this->check_out_time,
            'default_checkout_grace_minutes' => (int) $this->default_checkout_grace_minutes,
            'tax_rate' => (float) $this->tax_rate,
            'booking_prefix' => $this->booking_prefix,
            'invoice_prefix' => $this->invoice_prefix,
            'late_checkout_fee' => (float) $this->late_checkout_fee,
            'early_checkin_fee' => (float) $this->early_checkin_fee,
            'booking_cancellation_hours' => (int) $this->booking_cancellation_hours,
            'allow_overbooking' => (bool) $this->allow_overbooking,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
