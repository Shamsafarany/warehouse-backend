<?php

namespace App\Domains\Cart\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuantityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.required' => 'حقل الكمية إجباري.',
            'quantity.integer'  => 'يجب أن تكون الكمية رقماً صحيحاً.',
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