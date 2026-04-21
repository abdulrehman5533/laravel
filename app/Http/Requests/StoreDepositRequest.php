<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepositRequest extends FormRequest
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
            'deposit_type' => ['required', 'in:cash,cheque'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],

            // Cheque specific fields
            'cheque_number' => ['required_if:deposit_type,cheque', 'string', 'max:50'],
            'cheque_date' => ['required_if:deposit_type,cheque', 'date'],
            'cheque_issuer' => ['required_if:deposit_type,cheque', 'string', 'max:255'],
            'issuer_bank' => ['required_if:deposit_type,cheque', 'string', 'max:255'],
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
            'deposit_type.required' => 'Please select a deposit type.',
            'deposit_type.in' => 'Deposit type must be either cash or cheque.',
            'amount.required' => 'Please enter the deposit amount.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Amount must be at least 0.01.',
            'cheque_number.required_if' => 'Cheque number is required for cheque deposits.',
            'cheque_date.required_if' => 'Cheque date is required for cheque deposits.',
            'cheque_issuer.required_if' => 'Cheque issuer name is required for cheque deposits.',
            'issuer_bank.required_if' => 'Issuer bank is required for cheque deposits.',
        ];
    }
}
