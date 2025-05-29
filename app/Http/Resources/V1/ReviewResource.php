<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="Review",
 *     type="object",
 *     title="Review Resource",
 *     description="منبع نظرات شامل اطلاعات کامل نظر",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="شناسه نظر",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="service_request",
 *         type="object",
 *         description="اطلاعات درخواست سرویس",
 *         @OA\Property(
 *             property="id",
 *             type="integer",
 *             description="شناسه درخواست سرویس",
 *             example=1
 *         ),
 *         @OA\Property(
 *             property="title",
 *             type="string",
 *             description="عنوان درخواست سرویس",
 *             example="تعمیر لوله کشی"
 *         ),
 *         @OA\Property(
 *             property="status",
 *             type="string",
 *             description="وضعیت درخواست سرویس",
 *             example="completed"
 *         )
 *     ),
 *     @OA\Property(
 *         property="customer",
 *         type="object",
 *         description="اطلاعات مشتری",
 *         @OA\Property(
 *             property="id",
 *             type="integer",
 *             description="شناسه مشتری",
 *             example=1
 *         ),
 *         @OA\Property(
 *             property="username",
 *             type="string",
 *             description="نام کاربری مشتری",
 *             example="john_doe"
 *         ),
 *         @OA\Property(
 *             property="full_name",
 *             type="string",
 *             description="نام کامل مشتری",
 *             example="علی محمدی"
 *         )
 *     ),
 *     @OA\Property(
 *         property="service_provider",
 *         type="object",
 *         description="اطلاعات ارائه‌دهنده سرویس",
 *         @OA\Property(
 *             property="id",
 *             type="integer",
 *             description="شناسه ارائه‌دهنده سرویس",
 *             example=1
 *         ),
 *         @OA\Property(
 *             property="username",
 *             type="string",
 *             description="نام کاربری ارائه‌دهنده سرویس",
 *             example="jane_doe"
 *         ),
 *         @OA\Property(
 *             property="full_name",
 *             type="string",
 *             description="نام کامل ارائه‌دهنده سرویس",
 *             example="رضا احمدی"
 *         )
 *     ),
 *     @OA\Property(
 *         property="rating",
 *         type="number",
 *         format="float",
 *         description="امتیاز نظر",
 *         example=4.5
 *     ),
 *     @OA\Property(
 *         property="comment",
 *         type="string",
 *         description="متن نظر",
 *         example="کار بسیار عالی انجام شد"
 *     ),
 *     @OA\Property(
 *         property="rating_details",
 *         type="object",
 *         description="جزئیات امتیازدهی",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="is_verified",
 *         type="boolean",
 *         description="آیا نظر تایید شده است",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="is_visible",
 *         type="boolean",
 *         description="آیا نظر قابل نمایش است",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="تاریخ ایجاد نظر",
 *         example="2025-05-23T10:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="تاریخ آخرین بروزرسانی نظر",
 *         example="2025-05-23T10:00:00Z"
 *     )
 * )
 */
class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_request' => [
                'id' => $this->serviceRequest->id,
                'title' => $this->serviceRequest->title,
                'status' => $this->serviceRequest->status,
            ],
            'customer' => [
                'id' => $this->customer->id,
                'username' => $this->customer->username,
                'full_name' => $this->customer->full_name,
            ],
            'service_provider' => [
                'id' => $this->serviceProvider->id,
                'username' => $this->serviceProvider->username,
                'full_name' => $this->serviceProvider->full_name,
            ],
            'rating' => $this->rating,
            'comment' => $this->comment,
            'rating_details' => $this->rating_details,
            'is_verified' => $this->is_verified,
            'is_visible' => $this->is_visible,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 