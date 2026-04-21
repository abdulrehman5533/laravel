<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => 'nullable|exists:branches,id',
            'customer_id' => 'nullable|exists:customers,id',
            'currency' => 'nullable|string|in:USD,EUR,GBP,PKR,AED,PKR',
            'is_wholesale' => 'nullable|boolean',
            'invoice_type' => 'nullable|string|in:Tax,Non-Tax',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.exists' => 'Selected customer not found',
            'currency.in' => 'Invalid currency',
        ];
    }
}
