<?php

namespace App\Http\Requests\Api\V1\Billing;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => [
                'required',
                Rule::in(['cash', 'card', 'mpesa', 'bank_transfer']),
            ],
            'transaction_reference' => ['nullable', 'string'],
            'status' => [
                'sometimes',
                Rule::in(['pending', 'completed', 'failed', 'refunded']),
            ],
        ];
    }
}
