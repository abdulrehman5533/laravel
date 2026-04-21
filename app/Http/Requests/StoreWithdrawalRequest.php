<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWithdrawalRequest extends FormRequest
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
            'withdrawal_type' => ['required', 'in:cash,cheque,transfer'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payee_name' => ['required', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],

            // Cheque specific fields
            'cheque_number' => ['required_if:withdrawal_type,cheque', 'string', 'max:50'],
            'cheque_date' => ['required_if:withdrawal_type,cheque', 'date'],

            // Transfer specific fields
            'beneficiary_account' => ['required_if:withdrawal_type,transfer', 'string', 'max:255'],
            'beneficiary_bank' => ['required_if:withdrawal_type,transfer', 'string', 'max:255'],
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
            'withdrawal_type.required' => 'Please select a withdrawal type.',
            'withdrawal_type.in' => 'Withdrawal type must be cash, cheque, or transfer.',
            'amount.required' => 'Please enter the withdrawal amount.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Amount must be at least 0.01.',
            'payee_name.required' => 'Please enter the payee/recipient name.',
            'cheque_number.required_if' => 'Cheque number is required for cheque withdrawals.',
            'cheque_date.required_if' => 'Cheque date is required for cheque withdrawals.',
            'beneficiary_account.required_if' => 'Beneficiary account is required for transfers.',
            'beneficiary_bank.required_if' => 'Beneficiary bank is required for transfers.',
        ];
    }
}
