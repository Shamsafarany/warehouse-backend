<?php

namespace App\Domains\Identity\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'حقل كلمة المرور الحالية إجباري.',
            'current_password.current_password' => 'كلمة المرور الحالية غير صحيحة.',
            'password.required' => 'حقل كلمة المرور الجديدة إجباري.',
            'password.min' => 'يجب ألا تقل كلمة المرور عن 8 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
        ];
    }

}