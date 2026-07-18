<?php

namespace App\Http\Requests\Api\V1\Guests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGuestRequest extends FormRequest
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
        $guestId = $this->route('guest')?->id;

        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('guests', 'email')->ignore($guestId),
            ],
            'phone' => ['sometimes', 'string', 'max:20'],
            'nationality' => ['sometimes', 'nullable', 'string', 'max:255'],
            'national_id' => ['sometimes', 'nullable', 'string', 'max:50'],
            'passport_number' => ['sometimes', 'nullable', 'string', 'max:50'],
        ];
    }
}
