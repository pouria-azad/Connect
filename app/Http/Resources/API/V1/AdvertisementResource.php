<?php

namespace App\Http\Resources\API\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="AdvertisementResource",
 *     type="object",
 *     title="Advertisement Resource",
 *     description="منبع تبلیغات شامل اطلاعات کامل تبلیغ",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="شناسه تبلیغ",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="عنوان تبلیغ",
 *         example="تبلیغ نمونه"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="توضیحات تبلیغ",
 *         example="این یک تبلیغ نمونه است",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="image_url",
 *         type="string",
 *         description="آدرس تصویر تبلیغ",
 *         example="https://example.com/image.jpg"
 *     ),
 *     @OA\Property(
 *         property="target_url",
 *         type="string",
 *         description="آدرس مقصد تبلیغ",
 *         example="https://example.com"
 *     ),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         description="نوع تبلیغ",
 *         enum={"banner", "popup", "sidebar"},
 *         example="banner"
 *     ),
 *     @OA\Property(
 *         property="position",
 *         type="string",
 *         description="موقعیت تبلیغ",
 *         enum={"top", "bottom", "left", "right"},
 *         example="top",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="start_date",
 *         type="string",
 *         format="date-time",
 *         description="تاریخ شروع نمایش تبلیغ",
 *         example="2025-05-23T10:00:00Z",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="end_date",
 *         type="string",
 *         format="date-time",
 *         description="تاریخ پایان نمایش تبلیغ",
 *         example="2025-06-23T10:00:00Z",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="وضعیت تبلیغ",
 *         enum={"active", "inactive"},
 *         example="active"
 *     ),
 *     @OA\Property(
 *         property="display_count",
 *         type="integer",
 *         description="تعداد نمایش تبلیغ",
 *         example=1000
 *     ),
 *     @OA\Property(
 *         property="click_count",
 *         type="integer",
 *         description="تعداد کلیک روی تبلیغ",
 *         example=100
 *     ),
 *     @OA\Property(
 *         property="created_by_admin",
 *         ref="#/components/schemas/UserResource",
 *         description="ادمین ایجاد کننده تبلیغ",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="تاریخ ایجاد تبلیغ",
 *         example="2025-05-23T10:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="تاریخ آخرین بروزرسانی تبلیغ",
 *         example="2025-05-23T10:00:00Z"
 *     )
 * )
 */
class AdvertisementResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'target_url' => $this->target_url,
            'type' => $this->type,
            'position' => $this->position,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'display_count' => $this->display_count,
            'click_count' => $this->click_count,
            'created_by_admin' => new UserResource($this->whenLoaded('admin')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 