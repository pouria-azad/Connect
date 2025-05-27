<?php

namespace App\Http\Requests\V1\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class UseGiftCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'exists:gift_cards,code']
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'کد کارت هدیه الزامی است',
            'code.exists' => 'کد کارت هدیه نامعتبر است'
        ];
    }
} 