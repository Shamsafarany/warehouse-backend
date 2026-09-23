<?php

namespace App\Domains\Inventory\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockAdjustRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:in,out'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['required', 'string', 'max:500'],
            'reference_id' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'نوع الحركة إجباري.',
            'type.in' => 'نوع الحركة يجب أن يكون إدخال (in) أو إخراج (out).',
            'quantity.required' => 'الكمية إجبارية.',
            'quantity.integer' => 'يجب أن تكون الكمية رقماً صحيحاً.',
            'quantity.min' => 'يجب أن تكون الكمية أكبر من الصفر.',
            'notes.required' => 'ملاحظات أو سبب تعديل المخزون إجباري.',
        ];
    }
    protected function prepareForValidation(): void
    {
        $this->merge([
            'notes' => $this->notes ? strip_tags(trim($this->notes)) : null,
            'reference_id' => $this->reference_id ? strip_tags(trim($this->reference_id)) : null,
        ]);
    }
}