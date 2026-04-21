<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class CreateInstallmentPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pos_sale_id' => 'required|exists:pos_sales,id',
            'number_of_installments' => 'required|integer|min:2|max:24',
            'first_payment_date' => 'required|date|after:today',
            'frequency' => 'required|string|in:weekly,fortnightly,monthly,quarterly',
        ];
    }

    public function messages(): array
    {
        return [
            'pos_sale_id.required' => 'Sale ID is required',
            'number_of_installments.required' => 'Number of installments is required',
            'number_of_installments.min' => 'Minimum 2 installments required',
            'first_payment_date.required' => 'First payment date is required',
            'first_payment_date.after' => 'Payment date must be in the future',
            'frequency.required' => 'Payment frequency is required',
        ];
    }
}
