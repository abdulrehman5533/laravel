<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'nullable|exists:customers,id',
            'discount' => 'nullable|numeric|min:0',
        ];
    }
}
