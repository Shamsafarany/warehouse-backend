<?php

namespace App\Domains\Identity\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->user()->id)],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'البريد الإلكتروني مستخدم من قبل مستخدم آخر.',
        ];
    }
}