<?php

namespace App\Http\Requests\Api\V1\Rooms;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomTypeRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'capacity' => ['sometimes', 'integer', 'min:1'],
            'beds' => ['sometimes', 'integer', 'min:1'],
            'base_price' => ['sometimes', 'numeric', 'min:0'],
            'amenity_ids' => ['sometimes', 'nullable', 'array'],
            'amenity_ids.*' => ['integer', 'exists:amenities,id'],
        ];
    }
}
