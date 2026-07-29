<?php

namespace App\Http\Requests\Api\V1\Pricing;

use App\Models\RatePlan;
use Illuminate\Foundation\Http\FormRequest;

class StoreRatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $hotel = $this->route('hotel');

        return $this->user()->can('create', [RatePlan::class, $hotel]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'modifier_type' => ['required', 'in:percentage,fixed'],
            'modifier_value' => ['required', 'numeric'],
            'cancellation_policy' => ['nullable', 'string', 'max:255'],
            'meal_plan' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}
