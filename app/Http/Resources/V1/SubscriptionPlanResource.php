<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="SubscriptionPlanResource",
 *     type="object",
 *     title="Subscription Plan Resource",
 *     description="منبع طرح اشتراک شامل اطلاعات کامل طرح",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="شناسه طرح اشتراک",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="نام طرح اشتراک",
 *         example="طرح طلایی"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="توضیحات طرح اشتراک",
 *         example="طرح اشتراک ویژه با امکانات پیشرفته"
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="number",
 *         format="float",
 *         description="قیمت طرح اشتراک",
 *         example=100000
 *     ),
 *     @OA\Property(
 *         property="duration_days",
 *         type="integer",
 *         description="مدت زمان اعتبار طرح به روز",
 *         example=30
 *     ),
 *     @OA\Property(
 *         property="features",
 *         type="array",
 *         description="ویژگی‌های طرح اشتراک",
 *         @OA\Items(type="string"),
 *         example={"امکان ثبت آگهی نامحدود", "پشتیبانی ۲۴/۷", "نمایش در صفحه اول"}
 *     ),
 *     @OA\Property(
 *         property="is_active",
 *         type="boolean",
 *         description="وضعیت فعال بودن طرح",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="max_ads_count",
 *         type="integer",
 *         description="حداکثر تعداد آگهی‌های مجاز",
 *         example=100
 *     ),
 *     @OA\Property(
 *         property="max_services_count",
 *         type="integer",
 *         description="حداکثر تعداد سرویس‌های مجاز",
 *         example=50
 *     ),
 *     @OA\Property(
 *         property="priority_level",
 *         type="integer",
 *         description="سطح اولویت طرح",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="can_highlight_ads",
 *         type="boolean",
 *         description="امکان برجسته کردن آگهی‌ها",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="can_pin_ads",
 *         type="boolean",
 *         description="امکان سنجاق کردن آگهی‌ها",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="can_use_advanced_features",
 *         type="boolean",
 *         description="امکان استفاده از ویژگی‌های پیشرفته",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="تاریخ ایجاد طرح",
 *         example="2025-05-23T10:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="تاریخ آخرین بروزرسانی طرح",
 *         example="2025-05-23T10:00:00Z"
 *     )
 * )
 */
class SubscriptionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'duration_days' => $this->duration_days,
            'features' => $this->features,
            'is_active' => $this->is_active,
            'max_ads_count' => $this->max_ads_count,
            'max_services_count' => $this->max_services_count,
            'priority_level' => $this->priority_level,
            'can_highlight_ads' => $this->can_highlight_ads,
            'can_pin_ads' => $this->can_pin_ads,
            'can_use_advanced_features' => $this->can_use_advanced_features,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 