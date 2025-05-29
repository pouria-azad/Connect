<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserResource",
 *     type="object",
 *     title="User Resource",
 *     description="منبع کاربر شامل اطلاعات پایه کاربر",
 *     @OA\Property(
 *         property="full_name",
 *         type="string",
 *         description="نام و نام خانوادگی کاربر",
 *         example="علی محمدی"
 *     ),
 *     @OA\Property(
 *         property="mobile",
 *         type="string",
 *         description="شماره موبایل کاربر",
 *         example="09123456789"
 *     ),
 *     @OA\Property(
 *         property="national_id",
 *         type="string",
 *         description="کد ملی کاربر",
 *         example="0123456789",
 *         nullable=true
 *     )
 * )
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'full_name' => e($this->full_name),
            'mobile' => e($this->mobile),
            'national_id' => e($this->national_id),
        ];
    }
}
