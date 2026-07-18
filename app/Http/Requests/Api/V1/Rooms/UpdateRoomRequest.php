<?php

namespace App\Http\Requests\Api\V1\Rooms;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoomRequest extends FormRequest
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
        $roomId = $this->route('room')?->id;

        return [
            'room_type_id' => [
                'sometimes',
                'integer',
                Rule::exists('room_types', 'id')->where('hotel_id', $hotelId),
            ],
            'room_number' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('rooms', 'room_number')->ignore($roomId),
            ],
            'floor' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'status' => ['sometimes', Rule::in(['available', 'occupied', 'reserved', 'cleaning', 'maintenance'])],
        ];
    }
}
