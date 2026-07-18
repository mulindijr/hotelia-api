<?php

namespace App\Http\Requests\Api\V1\Hotels;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHotelSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'currency' => ['sometimes', 'string', 'size:3'],
            'timezone' => ['sometimes', 'string', 'timezone'],
            'language' => ['sometimes', 'string', 'max:5'],
            'check_in_time' => ['sometimes', 'date_format:H:i,H:i:s'],
            'check_out_time' => ['sometimes', 'date_format:H:i,H:i:s'],
            'default_checkout_grace_minutes' => ['sometimes', 'integer', 'min:0', 'max:1440'],
            'tax_rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'booking_prefix' => ['sometimes', 'string', 'max:10'],
            'invoice_prefix' => ['sometimes', 'string', 'max:10'],
            'late_checkout_fee' => ['sometimes', 'numeric', 'min:0'],
            'early_checkin_fee' => ['sometimes', 'numeric', 'min:0'],
            'booking_cancellation_hours' => ['sometimes', 'integer', 'min:0'],
            'allow_overbooking' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
