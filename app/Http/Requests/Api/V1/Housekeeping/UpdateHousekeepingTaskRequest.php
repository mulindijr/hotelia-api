<?php

namespace App\Http\Requests\Api\V1\Housekeeping;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHousekeepingTaskRequest extends FormRequest
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
            'room_id' => [
                'sometimes',
                'integer',
                Rule::exists('rooms', 'id')->where('hotel_id', $hotelId),
            ],
            'assigned_to' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('hotel_user', 'user_id')->where('hotel_id', $hotelId),
            ],
            'status' => ['sometimes', Rule::in(['pending', 'in_progress', 'completed'])],
            'scheduled_at' => ['sometimes', 'date'],
            'completed_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
