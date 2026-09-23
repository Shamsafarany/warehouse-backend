<?php

namespace App\Domains\Inventory\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stock_quantity' => ['prohibited'],
            'reserved_quantity' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'reserved_quantity.integer' => 'يجب أن تكون الكمية المحجوزة رقماً صحيحاً.',
            'reserved_quantity.min' => 'يجب ألا تقل الكمية المحجوزة عن الصفر.',
            'stock_quantity.prohibited' => 'لا يمكن تعديل المخزون الفعلي من هنا. يرجى استخدام مسار تعديل المخزون المخصص (Adjust Stock).',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('reserved_quantity')) {
            $this->merge([
                'reserved_quantity' => (int) $this->reserved_quantity,
            ]);
        }
    }
}