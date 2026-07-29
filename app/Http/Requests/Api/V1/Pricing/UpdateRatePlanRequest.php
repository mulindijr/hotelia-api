<?php

namespace App\Http\Requests\Api\V1\Pricing;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $hotel = $this->route('hotel');
        $ratePlan = $this->route('rate_plan');
        return $this->user()->can('update', [$ratePlan, $hotel]);
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['sometimes', 'required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'modifier_type' => ['sometimes', 'required', 'in:percentage,fixed'],
            'modifier_value' => ['sometimes', 'required', 'numeric'],
            'cancellation_policy' => ['nullable', 'string', 'max:255'],
            'meal_plan' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}
