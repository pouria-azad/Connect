<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserBankCardResource",
 *     type="object",
 *     title="User Bank Card Resource",
 *     description="منبع کارت بانکی کاربر شامل اطلاعات کارت",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="شناسه کارت بانکی",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="card_number",
 *         type="string",
 *         description="شماره کارت بانکی",
 *         example="6037991123456789"
 *     ),
 *     @OA\Property(
 *         property="bank_name",
 *         type="string",
 *         description="نام بانک",
 *         example="ملت"
 *     ),
 *     @OA\Property(
 *         property="holder_name",
 *         type="string",
 *         description="نام دارنده کارت",
 *         example="علی محمدی"
 *     ),
 *     @OA\Property(
 *         property="is_verified",
 *         type="boolean",
 *         description="وضعیت تایید کارت",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="is_default",
 *         type="boolean",
 *         description="آیا کارت پیش‌فرض است",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="تاریخ ثبت کارت",
 *         example="2025-05-23T10:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="تاریخ آخرین بروزرسانی",
 *         example="2025-05-23T10:00:00Z"
 *     )
 * )
 */
class UserBankCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'card_number' => $this->card_number,
            'bank_name' => $this->bank_name,
            'holder_name' => $this->holder_name,
            'is_verified' => $this->is_verified,
            'is_default' => $this->is_default,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 