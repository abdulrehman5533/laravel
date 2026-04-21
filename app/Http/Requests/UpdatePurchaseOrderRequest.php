<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_type' => 'required|string|in:Tax,Non-Tax',
            'po_date' => 'required|date',
            'expected_delivery_date' => 'required|date|after:po_date',
            'material_type' => 'required|in:Gold,Silver,Gemstones,Diamonds,Mixed',
            'description' => 'nullable|string|max:1000',
            'gst_percentage' => 'required|numeric|between:0,28',
            'discount_percentage' => 'numeric|between:0,100',
            'other_charges' => 'numeric|min:0',
            'other_charges_description' => 'nullable|string|max:500',
            'items' => 'array',
            'items.*.item_code' => 'required|string|max:50',
            'items.*.description' => 'required|string|max:500',
            'items.*.material_type' => 'required|in:Gold,Silver,Gemstones,Diamonds,Mixed',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.purity' => 'nullable|string|max:50',
            'items.*.batch_number' => 'nullable|string|max:100',
            'items.*.serial_number' => 'nullable|string|max:100',
            'items.*.gst_percentage' => 'numeric|between:0,28',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
        ];
    }
}
