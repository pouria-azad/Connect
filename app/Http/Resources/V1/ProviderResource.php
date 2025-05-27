<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ProviderResource",
 *     type="object",
 *     title="Provider Resource",
 *     description="منبع ارائه‌دهنده شامل اطلاعات کامل پروفایل، مدال‌ها و نظرات",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="شناسه ارائه‌دهنده",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="shop_name",
 *         type="string",
 *         description="نام فروشگاه یا کسب‌وکار",
 *         example="فروشگاه نمونه"
 *     ),
 *     @OA\Property(
 *         property="senfi_number",
 *         type="string",
 *         description="شماره صنفی ارائه‌دهنده",
 *         example="123456789"
 *     ),
 *     @OA\Property(
 *         property="occupation",
 *         type="object",
 *         description="اطلاعات شغل ارائه‌دهنده",
 *         nullable=true,
 *         @OA\Property(
 *             property="id",
 *             type="integer",
 *             description="شناسه شغل",
 *             example=1
 *         ),
 *         @OA\Property(
 *             property="name",
 *             type="string",
 *             description="نام شغل",
 *             example="برقکار"
 *         )
 *     ),
 *     @OA\Property(
 *         property="province",
 *         type="object",
 *         description="اطلاعات استان محل فعالیت",
 *         nullable=true,
 *         @OA\Property(
 *             property="id",
 *             type="integer",
 *             description="شناسه استان",
 *             example=1
 *         ),
 *         @OA\Property(
 *             property="name",
 *             type="string",
 *             description="نام استان",
 *             example="تهران"
 *         )
 *     ),
 *     @OA\Property(
 *         property="city",
 *         type="object",
 *         description="اطلاعات شهر محل فعالیت",
 *         nullable=true,
 *         @OA\Property(
 *             property="id",
 *             type="integer",
 *             description="شناسه شهر",
 *             example=1
 *         ),
 *         @OA\Property(
 *             property="name",
 *             type="string",
 *             description="نام شهر",
 *             example="تهران"
 *         )
 *     ),
 *     @OA\Property(
 *         property="can_serve_nation_wide",
 *         type="boolean",
 *         description="آیا ارائه‌دهنده می‌تواند خدمات را در سطح کشوری ارائه دهد",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="expertise_areas",
 *         type="array",
 *         description="حوزه‌های تخصص ارائه‌دهنده",
 *         @OA\Items(
 *             type="string",
 *             example="برق"
 *         )
 *     ),
 *     @OA\Property(
 *         property="last_activity_at",
 *         type="string",
 *         format="date-time",
 *         description="آخرین زمان فعالیت ارائه‌دهنده",
 *         example="2025-05-23T10:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="average_rating",
 *         type="number",
 *         format="float",
 *         description="میانگین امتیاز ارائه‌دهنده",
 *         example=4.5
 *     ),
 *     @OA\Property(
 *         property="total_reviews_count",
 *         type="integer",
 *         description="تعداد کل نظرات",
 *         example=100
 *     ),
 *     @OA\Property(
 *         property="successful_orders_count",
 *         type="integer",
 *         description="تعداد سفارش‌های موفق",
 *         example=200
 *     ),
 *     @OA\Property(
 *         property="is_top_provider_of_week",
 *         type="boolean",
 *         description="آیا ارائه‌دهنده برترین ارائه‌دهنده هفته است",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="medals",
 *         type="array",
 *         description="لیست مدال‌های ارائه‌دهنده",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(
 *                 property="id",
 *                 type="integer",
 *                 description="شناسه مدال",
 *                 example=1
 *             ),
 *             @OA\Property(
 *                 property="name",
 *                 type="string",
 *                 description="نام مدال",
 *                 example="مدال طلایی"
 *             ),
 *             @OA\Property(
 *                 property="awarded_at",
 *                 type="string",
 *                 format="date-time",
 *                 description="زمان اعطای مدال",
 *                 example="2025-05-23T10:00:00Z"
 *             )
 *         )
 *     ),
 *     @OA\Property(
 *         property="reviews",
 *         type="array",
 *         description="لیست نظرات کاربران درباره ارائه‌دهنده",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(
 *                 property="id",
 *                 type="integer",
 *                 description="شناسه نظر",
 *                 example=1
 *             ),
 *             @OA\Property(
 *                 property="rating",
 *                 type="number",
 *                 format="float",
 *                 description="امتیاز نظر",
 *                 example=4.0
 *             ),
 *             @OA\Property(
 *                 property="comment",
 *                 type="string",
 *                 description="متن نظر",
 *                 example="عالی بود!"
 *             ),
 *             @OA\Property(
 *                 property="created_at",
 *                 type="string",
 *                 format="date-time",
 *                 description="زمان ثبت نظر",
 *                 example="2025-05-23T10:00:00Z"
 *             )
 *         )
 *     )
 * )
 */
class ProviderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'shop_name' => $this->shop_name,
            'senfi_number' => $this->senfi_number,
            'occupation' => $this->occupation ? [
                'id' => $this->occupation->id,
                'name' => $this->occupation->name,
            ] : null,
            'province' => $this->province ? [
                'id' => $this->province->id,
                'name' => $this->province->name,
            ] : null,
            'city' => $this->city ? [
                'id' => $this->city->id,
                'name' => $this->city->name,
            ] : null,
            'can_serve_nation_wide' => $this->can_serve_nation_wide,
            'expertise_areas' => $this->expertise_areas,
            'last_activity_at' => $this->last_activity_at,
            'average_rating' => $this->average_rating,
            'total_reviews_count' => $this->total_reviews_count,
            'successful_orders_count' => $this->successful_orders_count,
            'is_top_provider_of_week' => $this->is_top_provider_of_week,
            'medals' => $this->medals->map(function ($medal) {
                return [
                    'id' => $medal->id,
                    'name' => $medal->name,
                    'awarded_at' => $medal->pivot->created_at,
                ];
            }),
            'reviews' => $this->reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => $this->rating,
                    'comment' => $this->comment,
                    'created_at' => $this->created_at,
                ];
            }),
        ];
    }
}
