<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $customerId = $this->route('customer');
        if ($customerId instanceof \App\Models\Customer) {
            $customerId = $customerId->id;
        }

        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email,'.$customerId,
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'customer_type' => 'required|in:individual,business',
            'company_name' => 'required_if:customer_type,business|nullable|string|max:255',
            'tax_id' => 'nullable|string|max:50|unique:customers,tax_id,'.$customerId,
            'credit_limit' => 'nullable|numeric|min:0',
            'membership_level' => 'nullable|in:bronze,silver,gold,platinum',
            'preferred_payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'referral_source' => 'nullable|string|max:100',
            'special_discount_percentage' => 'nullable|numeric|min:0|max:100',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'boolean',
            'marketing_consent' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.unique' => 'This email address is already registered.',
            'company_name.required_if' => 'Company name is required for business customers.',
            'tax_id.unique' => 'This tax ID is already registered.',
            'credit_limit.min' => 'Credit limit must be a positive number.',
            'special_discount_percentage.max' => 'Special discount cannot exceed 100%.',
        ];
    }

    public function attributes(): array
    {
        return [
            'address_line_1' => 'address line 1',
            'address_line_2' => 'address line 2',
            'postal_code' => 'postal code',
            'customer_type' => 'customer type',
            'company_name' => 'company name',
            'tax_id' => 'tax ID',
            'credit_limit' => 'credit limit',
            'membership_level' => 'membership level',
            'preferred_payment_method' => 'preferred payment method',
            'referral_source' => 'referral source',
            'special_discount_percentage' => 'special discount percentage',
            'branch_id' => 'branch',
            'is_active' => 'active status',
            'marketing_consent' => 'marketing consent',
        ];
    }
}
