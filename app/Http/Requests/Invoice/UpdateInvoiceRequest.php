<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceRequest extends FormRequest
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
        $invoiceId = $this->route('invoice');

        return [
            'customer_id' => ['sometimes', 'required', 'integer', 'exists:customers,id'],
            'invoice_number' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('invoices', 'invoice_number')->ignore($invoiceId),
            ],
            'invoice_date' => ['sometimes', 'required', 'date'],
            'subtotal' => ['sometimes', 'required', 'numeric', 'min:0'],
            'tax' => ['sometimes', 'required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_id.exists' => 'The selected customer does not exist.',
        ];
    }
}
