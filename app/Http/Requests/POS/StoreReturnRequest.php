<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class StoreReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pos_sale_id' => 'required|exists:pos_sales,id',
            'pos_sale_item_id' => 'required|exists:pos_sale_items,id',
            'type' => 'required|string|in:return,exchange,repair',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
            'refund_method' => 'nullable|string|in:original,cash,bank_transfer',
            'refund_amount' => 'nullable|numeric|min:0',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'quantity_returned' => 'required|numeric|min:0.001',
            'branch_id' => 'nullable|exists:branches,id',
            'customer_id' => 'nullable|exists:customers,id',
        ];
    }

    public function messages(): array
    {
        return [
            'pos_sale_id.required' => 'Sale ID is required',
            'pos_sale_id.exists' => 'Sale not found',
            'type.required' => 'Return type is required',
            'reason.required' => 'Reason is required',
        ];
    }
}
