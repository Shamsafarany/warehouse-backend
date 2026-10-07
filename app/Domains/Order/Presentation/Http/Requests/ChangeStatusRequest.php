<?php

namespace App\Domains\Order\Presentation\Requests;

use App\Domains\Order\Domain\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;


class ChangeStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::enum(OrderStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'حالة الطلب الجديدة مطلوبة.',
            'status.string' => 'حالة الطلب يجب أن تكون نصاً.',
            'status.enum' => 'حالة الطلب المدخلة غير صالحة أو غير معروفة.',
        ];
    }
}