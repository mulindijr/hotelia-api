<?php

namespace App\Http\Requests\Api\V1\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user),
            ],
            'phone' => ['sometimes', 'string', 'max:255'],
            'role' => [
                'sometimes',
                'string',
                Rule::in(['hotel_manager', 'receptionist', 'housekeeper', 'accountant']),
            ],
            'is_active' => ['sometimes', 'boolean'],
            'password' => ['sometimes', 'string', 'min:8', 'nullable'],
        ];
    }
}
