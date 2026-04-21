<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransferRequest extends FormRequest
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
            'from_account_id' => ['required', 'exists:bank_accounts,id', 'different:to_account_id'],
            'to_account_id' => ['required', 'exists:bank_accounts,id', 'different:from_account_id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transfer_type' => ['required', 'in:internal,external'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],

            // External transfer fields
            'beneficiary_name' => ['required_if:transfer_type,external', 'string', 'max:255'],
            'beneficiary_account' => ['required_if:transfer_type,external', 'string', 'max:255'],
            'beneficiary_bank' => ['required_if:transfer_type,external', 'string', 'max:255'],
            'transfer_method' => ['required_if:transfer_type,external', 'in:neft,rtgs,imps,upi'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'from_account_id.required' => 'Please select a source account.',
            'from_account_id.exists' => 'The selected source account does not exist.',
            'from_account_id.different' => 'Source and destination accounts must be different.',
            'to_account_id.required' => 'Please select a destination account.',
            'to_account_id.exists' => 'The selected destination account does not exist.',
            'to_account_id.different' => 'Source and destination accounts must be different.',
            'amount.required' => 'Please enter the transfer amount.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Amount must be at least 0.01.',
            'transfer_type.required' => 'Please select transfer type.',
            'transfer_type.in' => 'Transfer type must be internal or external.',
            'beneficiary_name.required_if' => 'Beneficiary name is required for external transfers.',
            'beneficiary_account.required_if' => 'Beneficiary account is required for external transfers.',
            'beneficiary_bank.required_if' => 'Beneficiary bank is required for external transfers.',
            'transfer_method.required_if' => 'Transfer method is required for external transfers.',
        ];
    }
}
