<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReconcileRequest extends FormRequest
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
            'statement_date' => ['required', 'date'],
            'statement_balance' => ['required', 'numeric', 'min:0'],
            'outstanding_deposits' => ['nullable', 'numeric', 'min:0'],
            'outstanding_cheques' => ['nullable', 'numeric', 'min:0'],
            'bank_charges' => ['nullable', 'numeric', 'min:0'],
            'interest_earned' => ['nullable', 'numeric'],
            'reconciliation_notes' => ['nullable', 'string', 'max:1000'],
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
            'statement_date.required' => 'Please select the statement date.',
            'statement_date.date' => 'Statement date must be a valid date.',
            'statement_balance.required' => 'Please enter the statement balance.',
            'statement_balance.numeric' => 'Statement balance must be a valid number.',
            'statement_balance.min' => 'Statement balance must be at least 0.',
            'outstanding_deposits.numeric' => 'Outstanding deposits must be a valid number.',
            'outstanding_cheques.numeric' => 'Outstanding cheques must be a valid number.',
            'bank_charges.numeric' => 'Bank charges must be a valid number.',
            'interest_earned.numeric' => 'Interest earned must be a valid number.',
        ];
    }
}
