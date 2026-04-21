<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChequeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'bank_account_id' => ['required', 'exists:bank_accounts,id'],
            'cheque_number' => ['required', 'string', 'max:50', 'unique:cheque_management,cheque_number'],
            'cheque_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payee_name' => ['required', 'string', 'max:255'],
            'payee_account_number' => ['nullable', 'string', 'max:50'],
            'payee_bank' => ['nullable', 'string', 'max:255'],
            'memo' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'bank_account_id.required' => 'Please select a bank account.',
            'bank_account_id.exists' => 'The selected bank account does not exist.',
            'cheque_number.required' => 'Please enter the cheque number.',
            'cheque_number.unique' => 'This cheque number has already been issued.',
            'cheque_date.required' => 'Please select the cheque date.',
            'cheque_date.date' => 'Cheque date must be a valid date.',
            'amount.required' => 'Please enter the cheque amount.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Amount must be at least 0.01.',
            'payee_name.required' => 'Please enter the payee name.',
        ];
    }
}
