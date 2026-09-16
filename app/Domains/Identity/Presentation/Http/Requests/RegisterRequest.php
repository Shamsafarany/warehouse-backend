<?php

namespace App\Domains\Identity\Presentation\Http\Requests;

use App\Domains\Identity\Domain\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['sometimes', Rule::enum(UserRole::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'حقل الاسم إجباري.',
            'email.required' => 'حقل البريد الإلكتروني إجباري.',
            'email.email' => 'صيغة البريد الإلكتروني غير صالحة.',
            'email.unique' => 'البريد الإلكتروني مستخدم مسبقاً.',
            'password.required' => 'حقل كلمة المرور إجباري.',
            'password.min' => 'يجب أن تكون كلمة المرور 8 أحرف على الأقل.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
            'role.in' => 'الدور المحدد غير صالح، يجب أن يكون إما admin أو customer.',
        ];
    }

    protected function prepareForValidation(): void
{
    $this->merge([
        'first_name' => strip_tags(trim($this->first_name)),
        'last_name' => strip_tags(trim($this->last_name)),
        'email' => strtolower(trim($this->email)),
    ]);
}
}