<?php

namespace App\Services\Pricing;

use App\Models\Hotel;
use App\Models\PricingRule;
use App\Models\RatePlan;
use App\Models\RoomType;
use Carbon\Carbon;

class PricingService
{
    /**
     * Calculate the calculated nightly price for a room type given date, rate plan, and stay duration.
     */
    public function calculateNightlyPrice(
        RoomType $roomType,
        ?RatePlan $ratePlan,
        Carbon $date,
        int $stayNights = 1
    ): float {
        $price = (float) $roomType->base_price;

        // 1. Apply RatePlan modifier if active
        if ($ratePlan && $ratePlan->is_active) {
            if ($ratePlan->modifier_type === 'percentage') {
                $price += $price * ((float) $ratePlan->modifier_value / 100);
            } elseif ($ratePlan->modifier_type === 'fixed') {
                $price += (float) $ratePlan->modifier_value;
            }
        }

        // 2. Fetch applicable active pricing rules for the hotel
        $dayName = strtolower($date->format('l'));
        $dateStr = $date->toDateString();

        $rules = PricingRule::where('hotel_id', $roomType->hotel_id)
            ->where('is_active', true)
            ->where(function ($q) use ($ratePlan) {
                $q->whereNull('rate_plan_id');
                if ($ratePlan) {
                    $q->orWhere('rate_plan_id', $ratePlan->id);
                }
            })
            ->where(function ($q) use ($roomType) {
                $q->whereNull('room_type_id')
                    ->orWhere('room_type_id', $roomType->id);
            })
            ->where(function ($q) use ($dateStr) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', $dateStr);
            })
            ->where(function ($q) use ($dateStr) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $dateStr);
            })
            ->where(function ($q) use ($stayNights) {
                $q->whereNull('min_nights')
                    ->orWhere('min_nights', '<=', $stayNights);
            })
            ->where(function ($q) use ($stayNights) {
                $q->whereNull('max_nights')
                    ->orWhere('max_nights', '>=', $stayNights);
            })
            ->orderBy('priority', 'asc')
            ->get();

        // Filter by day of week if specified in JSON
        foreach ($rules as $rule) {
            if ($rule->days_of_week && is_array($rule->days_of_week)) {
                $normalizedDays = array_map('strtolower', $rule->days_of_week);
                if (! in_array($dayName, $normalizedDays, true)) {
                    continue;
                }
            }

            // Apply price modifier
            $val = (float) $rule->price_modifier_value;
            if ($rule->price_modifier_type === 'override') {
                $price = $val;
            } elseif ($rule->price_modifier_type === 'percentage') {
                $price += $price * ($val / 100);
            } elseif ($rule->price_modifier_type === 'fixed') {
                $price += $val;
            }
        }

        return max(0.00, round($price, 2));
    }
}
