<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "RegisterRequest",
    required: ["company_name", "first_name", "last_name", "email", "password", "domain"],
    properties: [
        new OA\Property(property: "company_name", type: "string", example: "Marriott Group"),
        new OA\Property(property: "domain", type: "string", example: "marriott"),
        new OA\Property(property: "first_name", type: "string", example: "John"),
        new OA\Property(property: "last_name", type: "string", example: "Doe"),
        new OA\Property(property: "email", type: "string", format: "email", example: "admin@marriott.com"),
        new OA\Property(property: "phone", type: "string", example: "+1234567890"),
        new OA\Property(property: "password", type: "string", format: "password", example: "Secret123!"),
    ]
)]
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255', 'unique:domains,domain'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
