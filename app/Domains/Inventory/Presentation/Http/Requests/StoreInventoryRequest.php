<?php

namespace App\Domains\Inventory\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'string', 'exists:products,id', 'unique:inventories,product_id'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'reserved_quantity' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'حقل المنتج إجباري.',
            'product_id.exists' => 'المنتج المختار غير موجود.',
            'product_id.unique' => 'يوجد سجل مخزون مسجل مسبقاً لهذا المنتج.',
            'stock_quantity.required' => 'حقل كمية المخزون إجباري.',
            'stock_quantity.integer' => 'يجب أن تكون كمية المخزون رقماً صحيحاً.',
            'stock_quantity.min' => 'يجب ألا تقل كمية المخزون عن الصفر.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'stock_quantity' => $this->stock_quantity !== null ? (int) $this->stock_quantity : 0,
            'reserved_quantity' => $this->reserved_quantity !== null ? (int) $this->reserved_quantity : 0,
        ]);
    }
}