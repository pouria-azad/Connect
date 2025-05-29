<?php

namespace App\Http\Resources\API\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V1\SubscriptionPlanResource;

/**
 * @OA\Schema(
 *     schema="SubscriptionResource",
 *     type="object",
 *     title="Subscription Resource",
 *     description="منبع اشتراک شامل اطلاعات کامل اشتراک",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="شناسه اشتراک",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         description="شناسه کاربر",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="subscription_plan_id",
 *         type="integer",
 *         description="شناسه طرح اشتراک",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="plan",
 *         ref="#/components/schemas/SubscriptionPlanResource",
 *         description="اطلاعات طرح اشتراک"
 *     ),
 *     @OA\Property(
 *         property="start_date",
 *         type="string",
 *         format="date-time",
 *         description="تاریخ شروع اشتراک",
 *         example="2025-05-23T10:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="end_date",
 *         type="string",
 *         format="date-time",
 *         description="تاریخ پایان اشتراک",
 *         example="2025-06-23T10:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="وضعیت اشتراک",
 *         enum={"active", "expired", "cancelled", "pending"},
 *         example="active"
 *     ),
 *     @OA\Property(
 *         property="payment_id",
 *         type="string",
 *         description="شناسه پرداخت",
 *         example="pay_123456789",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="amount",
 *         type="number",
 *         format="float",
 *         description="مبلغ پرداختی",
 *         example=100000
 *     ),
 *     @OA\Property(
 *         property="payment_method",
 *         type="string",
 *         description="روش پرداخت",
 *         example="online",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="payment_status",
 *         type="string",
 *         description="وضعیت پرداخت",
 *         enum={"pending", "completed", "failed"},
 *         example="completed"
 *     ),
 *     @OA\Property(
 *         property="payment_date",
 *         type="string",
 *         format="date-time",
 *         description="تاریخ پرداخت",
 *         example="2025-05-23T10:00:00Z",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="auto_renew",
 *         type="boolean",
 *         description="آیا تمدید خودکار فعال است",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="cancelled_at",
 *         type="string",
 *         format="date-time",
 *         description="تاریخ لغو اشتراک",
 *         example="2025-05-23T10:00:00Z",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="cancellation_reason",
 *         type="string",
 *         description="دلیل لغو اشتراک",
 *         example="درخواست کاربر",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="تاریخ ایجاد اشتراک",
 *         example="2025-05-23T10:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="تاریخ آخرین بروزرسانی اشتراک",
 *         example="2025-05-23T10:00:00Z"
 *     )
 * )
 */
class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'subscription_plan_id' => $this->subscription_plan_id,
            'plan' => new \App\Http\Resources\V1\SubscriptionPlanResource($this->plan),
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'payment_id' => $this->payment_id,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'payment_date' => $this->payment_date,
            'auto_renew' => $this->auto_renew,
            'cancelled_at' => $this->cancelled_at,
            'cancellation_reason' => $this->cancellation_reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 