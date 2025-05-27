<?php

namespace App\Http\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'integer'],
            'auto_renew' => ['boolean'],
            'payment_method' => ['sometimes', 'string', 'in:wallet,bank_card'],
            'payment_id' => ['required_if:payment_method,bank_card', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'plan_id.required' => 'لطفا پلن اشتراک را انتخاب کنید',
            'plan_id.exists' => 'پلن اشتراک انتخاب شده معتبر نیست',
            'auto_renew.boolean' => 'مقدار تمدید خودکار اشتراک نامعتبر است',
            'payment_method.required' => 'لطفا روش پرداخت را انتخاب کنید',
            'payment_method.in' => 'روش پرداخت انتخاب شده معتبر نیست',
            'payment_id.required_if' => 'شناسه پرداخت برای کارت بانکی الزامی است',
        ];
    }
} 