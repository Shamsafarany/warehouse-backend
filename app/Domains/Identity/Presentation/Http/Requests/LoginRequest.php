<?php

namespace App\Domains\Identity\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'حقل البريد الإلكتروني إجباري.',
            'email.email' => 'صيغة البريد الإلكتروني غير صالحة.',
            'password.required' => 'حقل كلمة المرور إجباري.',
        ];
    }

}