<?php

namespace App\Http\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateSubscriptionRequest",
 *     type="object",
 *     title="Update Subscription Request",
 *     description="درخواست بروزرسانی اشتراک",
 *     @OA\Property(
 *         property="auto_renew",
 *         type="boolean",
 *         description="آیا تمدید خودکار فعال باشد",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="وضعیت اشتراک",
 *         enum={"active", "cancelled"},
 *         example="active"
 *     ),
 *     @OA\Property(
 *         property="cancellation_reason",
 *         type="string",
 *         description="دلیل لغو اشتراک",
 *         example="درخواست کاربر",
 *         nullable=true
 *     )
 * )
 */
class UpdateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'auto_renew' => ['boolean'],
            'status' => ['string', 'in:active,cancelled'],
            'cancellation_reason' => ['required_if:status,cancelled', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'auto_renew.boolean' => 'مقدار تمدید خودکار اشتراک نامعتبر است',
            'status.in' => 'وضعیت اشتراک نامعتبر است',
            'cancellation_reason.required_if' => 'دلیل لغو اشتراک الزامی است',
            'cancellation_reason.max' => 'دلیل لغو اشتراک نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد',
        ];
    }
} 