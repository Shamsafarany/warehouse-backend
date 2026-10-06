<?php

namespace App\Domains\Cart\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'string', 'exists:products,id'],
            'quantity'   => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required'     => 'حقل المنتج إجباري.',
            'product_id.string'       => 'معرف المنتج يجب أن يكون نصاً.',
            'product_id.exists'       => 'المنتج المختار غير موجود.',
            'quantity.required'       => 'حقل الكمية إجباري.',
            'quantity.integer'        => 'يجب أن تكون الكمية رقماً صحيحاً.',
            'quantity.min'            => 'يجب ألا تقل الكمية عن 1.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'quantity' => $this->quantity !== null ? (int) $this->quantity : null,
        ]);
    }
}