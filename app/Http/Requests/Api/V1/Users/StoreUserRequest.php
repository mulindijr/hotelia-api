<?php

namespace App\Http\Requests\Api\V1\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'role' => [
                'required',
                'string',
                Rule::in(['hotel_manager', 'receptionist', 'housekeeper', 'accountant']),
            ],
            'password' => ['sometimes', 'string', 'min:8'],
        ];
    }
}
