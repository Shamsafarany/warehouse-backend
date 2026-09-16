<?php

namespace App\Domains\Catalog\Presentation\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id ?? $this->route('category');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($categoryId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'يجب أن يكون اسم التصنيف نصاً صحيحاً.',
            'name.max' => 'يجب ألا يتجاوز اسم التصنيف 255 حرفاً.',
            'name.unique' => 'اسم التصنيف مستخدم مسبقاً، يرجى اختيار اسم آخر.',
            'description.string' => 'يجب أن يكون الوصف نصاً صحيحاً.',
            'description.max' => 'يجب ألا يتجاوز الوصف 1000 حرفاً.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->name ? trim(strip_tags($this->name)) : null,
            'description' => $this->description ? trim(strip_tags($this->description)) : null,
        ]);
    }
}
