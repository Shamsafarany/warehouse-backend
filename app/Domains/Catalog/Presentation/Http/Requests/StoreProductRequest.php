<?php

namespace App\Domains\Catalog\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'string', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'حقل التصنيف إجباري.',
            'category_id.exists' => 'التصنيف المحدد غير موجود في النظام.',
            'name.required' => 'حقل اسم المنتج إجباري.',
            'name.max' => 'يجب ألا يتجاوز اسم المنتج 255 حرفاً.',
            'price.required' => 'حقل السعر إجباري.',
            'price.numeric' => 'يجب أن يكون السعر قيمة رقمية.',
            'price.min' => 'يجب ألا يقل السعر عن الصفر.',
            'is_active.boolean' => 'حقل حالة التفعيل يجب أن يكون صحيحاً أو خطأ.',
        ];
    }
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->name ? strip_tags(trim($this->name)) : null,
            'description' => $this->description ? strip_tags(trim($this->description)) : null,
        ]);
    }
}