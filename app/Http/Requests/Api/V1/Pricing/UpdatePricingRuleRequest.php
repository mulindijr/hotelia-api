<?php

namespace App\Http\Requests\Api\V1\Pricing;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePricingRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $hotel = $this->route('hotel');
        $pricingRule = $this->route('pricing_rule');
        return $this->user()->can('update', [$pricingRule, $hotel]);
    }

    public function rules(): array
    {
        return [
            'rate_plan_id' => ['nullable', 'integer', 'exists:rate_plans,id'],
            'room_type_id' => ['nullable', 'integer', 'exists:room_types,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'start_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'days_of_week' => ['nullable', 'array'],
            'days_of_week.*' => ['string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'min_nights' => ['nullable', 'integer', 'min:1'],
            'max_nights' => ['nullable', 'integer', 'min:1'],
            'price_modifier_type' => ['sometimes', 'required', 'in:percentage,fixed,override'],
            'price_modifier_value' => ['sometimes', 'required', 'numeric'],
            'priority' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
