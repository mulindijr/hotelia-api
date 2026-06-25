<?php

namespace App\Http\Requests\Api\V1\Hotels;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHotelRequest extends FormRequest
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
        $hotelId = $this->route('hotel')?->id;
        
        return [
            'name' => ['sometimes', 'string', 'max:255'],

            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('hotels', 'slug')->ignore($hotelId),
            ],

            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],

            'country' => ['sometimes', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:255'],
            'address' => ['sometimes', 'string'],

            'description' => ['sometimes', 'nullable', 'string'],

            'logo' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
