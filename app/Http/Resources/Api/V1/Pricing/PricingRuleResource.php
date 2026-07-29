<?php

namespace App\Http\Resources\Api\V1\Pricing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'hotel_id' => $this->hotel_id,
            'rate_plan_id' => $this->rate_plan_id,
            'room_type_id' => $this->room_type_id,
            'name' => $this->name,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'days_of_week' => $this->days_of_week,
            'min_nights' => $this->min_nights,
            'max_nights' => $this->max_nights,
            'price_modifier_type' => $this->price_modifier_type,
            'price_modifier_value' => (float) $this->price_modifier_value,
            'priority' => (int) $this->priority,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
