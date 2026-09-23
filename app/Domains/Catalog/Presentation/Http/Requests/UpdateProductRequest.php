<?php

namespace App\Domains\Catalog\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'string', 'exists:categories,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'التصنيف المحدد غير موجود في النظام.',
            'name.max' => 'يجب ألا يتجاوز اسم المنتج 255 حرفاً.',
            'price.numeric' => 'يجب أن يكون السعر قيمة رقمية.',
            'price.min' => 'يجب ألا يقل السعر عن الصفر.',
            'is_active.boolean' => 'حقل حالة التفعيل يجب أن يكون صحيحاً أو خطأ.',
        ];
    }
    protected function prepareForValidation(): void
    {
        $mergeData = [];

        if ($this->has('name')) {
            $mergeData['name'] = strip_tags(trim($this->name));
        }

        if ($this->has('description')) {
            $mergeData['description'] = $this->description ? strip_tags(trim($this->description)) : null;
        }

        if (!empty($mergeData)) {
            $this->merge($mergeData);
        }
    }
}