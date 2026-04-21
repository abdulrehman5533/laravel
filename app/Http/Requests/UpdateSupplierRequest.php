<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:suppliers,name,'.$this->supplier->id,
            'company_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:suppliers,email,'.$this->supplier->id,
            'phone_primary' => 'required|string|regex:/^[0-9]{10}$/',
            'phone_secondary' => 'nullable|string|regex:/^[0-9]{10}$/',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'country' => 'required|string|max:100',
            'gstin' => 'nullable|unique:suppliers,gstin,'.$this->supplier->id.'|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            'ntn' => 'nullable|unique:suppliers,ntn,'.$this->supplier->id.'|string|max:20',
            'pan' => 'nullable|unique:suppliers,pan,'.$this->supplier->id.'|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'bank_account_number' => 'nullable|string|max:20',
            'bank_name' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/',
            'payment_terms' => 'nullable|string|max:100',
            'payment_days' => 'integer|min:0|max:180',
            'supplier_type' => 'required|in:Gold,Silver,Diamond,Gems,Jewelry,Machinery,Packaging,Services,Karigar,Other',
            'product_specialty' => 'nullable|string|max:500',
            'opening_balance' => 'nullable|numeric|min:0',
            'opening_balance_date' => 'nullable|date',
            'rating' => 'numeric|between:0,5',
            'quality_score' => 'integer|between:0,100',
            'delivery_score' => 'integer|between:0,100',
            'status' => 'in:Active,Inactive,Blocked,OnHold',
            'gst_registered' => 'boolean',
            'contact_person' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }
}
