<?php

namespace App\Http\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreSubscriptionRequest",
 *     type="object",
 *     title="Store Subscription Request",
 *     description="درخواست ایجاد اشتراک جدید",
 *     required={"plan_id"},
 *     @OA\Property(
 *         property="plan_id",
 *         type="integer",
 *         description="شناسه طرح اشتراک",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="auto_renew",
 *         type="boolean",
 *         description="آیا تمدید خودکار فعال باشد",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="payment_method",
 *         type="string",
 *         description="روش پرداخت",
 *         enum={"wallet", "bank_card"},
 *         example="wallet"
 *     ),
 *     @OA\Property(
 *         property="payment_id",
 *         type="string",
 *         description="شناسه پرداخت (برای پرداخت با کارت بانکی)",
 *         example="pay_123456789"
 *     )
 * )
 */
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