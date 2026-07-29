<?php

namespace App\Http\Resources\Api\V1\Pricing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RatePlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'hotel_id' => $this->hotel_id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'modifier_type' => $this->modifier_type,
            'modifier_value' => (float) $this->modifier_value,
            'cancellation_policy' => $this->cancellation_policy,
            'meal_plan' => $this->meal_plan,
            'is_active' => (bool) $this->is_active,
            'is_default' => (bool) $this->is_default,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
