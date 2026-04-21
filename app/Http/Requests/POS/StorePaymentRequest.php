<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pos_sale_id' => 'required|exists:pos_sales,id',
            'payment_method' => 'required|string|in:cash,check,bank_transfer,credit_card,debit_card,online,card,upi,crypto,jazzcash,easypaisa,bank_js',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|in:USD,EUR,GBP,PKR,INR,AED,SAR,QAR,OMR,KWD,BHD',
            'exchange_rate' => 'nullable|numeric|min:0.000001',
            'bank_name' => 'nullable|string|max:255',
            'cheque_number' => 'nullable|string|max:20',
            'transaction_id' => 'nullable|string|max:100',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'pos_sale_id.required' => 'Sale ID is required',
            'pos_sale_id.exists' => 'Sale not found',
            'payment_method.required' => 'Payment method is required',
            'amount.required' => 'Payment amount is required',
            'amount.min' => 'Payment amount must be greater than 0',
        ];
    }
}
