<?php

namespace App\Domains\order\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_address' => ['nullable', 'array'],
            'billing_address' => ['nullable', 'array'],
        ];
    }
}