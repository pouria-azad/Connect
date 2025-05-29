<?php

namespace App\Http\Requests\V1\SubscriptionPlan;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreSubscriptionPlanRequest",
 *     type="object",
 *     title="Store Subscription Plan Request",
 *     description="درخواست ایجاد طرح اشتراک جدید",
 *     required={"name", "price", "duration_days", "max_ads_count", "max_services_count", "priority_level"},
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="نام طرح اشتراک",
 *         example="طرح طلایی",
 *         minLength=2,
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="توضیحات طرح اشتراک",
 *         example="طرح اشتراک ویژه با امکانات پیشرفته",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="number",
 *         format="float",
 *         description="قیمت طرح اشتراک",
 *         example=100000,
 *         minimum=0
 *     ),
 *     @OA\Property(
 *         property="duration_days",
 *         type="integer",
 *         description="مدت زمان اعتبار طرح به روز",
 *         example=30,
 *         minimum=1
 *     ),
 *     @OA\Property(
 *         property="features",
 *         type="array",
 *         description="ویژگی‌های طرح اشتراک",
 *         @OA\Items(type="string"),
 *         example={"امکان ثبت آگهی نامحدود", "پشتیبانی ۲۴/۷", "نمایش در صفحه اول"},
 *         nullable=true
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
 *         example=100,
 *         minimum=0
 *     ),
 *     @OA\Property(
 *         property="max_services_count",
 *         type="integer",
 *         description="حداکثر تعداد سرویس‌های مجاز",
 *         example=50,
 *         minimum=0
 *     ),
 *     @OA\Property(
 *         property="priority_level",
 *         type="integer",
 *         description="سطح اولویت طرح",
 *         example=1,
 *         minimum=0
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
 *     )
 * )
 */
class StoreSubscriptionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string'],
            'is_active' => ['boolean'],
            'max_ads_count' => ['required', 'integer', 'min:0'],
            'max_services_count' => ['required', 'integer', 'min:0'],
            'priority_level' => ['required', 'integer', 'min:0'],
            'can_highlight_ads' => ['boolean'],
            'can_pin_ads' => ['boolean'],
            'can_use_advanced_features' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام پلن اشتراک الزامی است',
            'name.string' => 'نام پلن اشتراک باید متن باشد',
            'name.max' => 'نام پلن اشتراک نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد',
            'description.string' => 'توضیحات باید متن باشد',
            'price.required' => 'قیمت الزامی است',
            'price.numeric' => 'قیمت باید عدد باشد',
            'price.min' => 'قیمت نمی‌تواند منفی باشد',
            'duration_days.required' => 'مدت زمان اشتراک الزامی است',
            'duration_days.integer' => 'مدت زمان اشتراک باید عدد صحیح باشد',
            'duration_days.min' => 'مدت زمان اشتراک باید حداقل ۱ روز باشد',
            'features.array' => 'ویژگی‌ها باید به صورت آرایه باشند',
            'features.*.string' => 'هر ویژگی باید متن باشد',
            'is_active.boolean' => 'وضعیت فعال بودن باید بله یا خیر باشد',
            'max_ads_count.required' => 'حداکثر تعداد آگهی‌ها الزامی است',
            'max_ads_count.integer' => 'حداکثر تعداد آگهی‌ها باید عدد صحیح باشد',
            'max_ads_count.min' => 'حداکثر تعداد آگهی‌ها نمی‌تواند منفی باشد',
            'max_services_count.required' => 'حداکثر تعداد خدمات الزامی است',
            'max_services_count.integer' => 'حداکثر تعداد خدمات باید عدد صحیح باشد',
            'max_services_count.min' => 'حداکثر تعداد خدمات نمی‌تواند منفی باشد',
            'priority_level.required' => 'سطح اولویت الزامی است',
            'priority_level.integer' => 'سطح اولویت باید عدد صحیح باشد',
            'priority_level.min' => 'سطح اولویت نمی‌تواند منفی باشد',
            'can_highlight_ads.boolean' => 'امکان برجسته کردن آگهی‌ها باید بله یا خیر باشد',
            'can_pin_ads.boolean' => 'امکان پین کردن آگهی‌ها باید بله یا خیر باشد',
            'can_use_advanced_features.boolean' => 'امکان استفاده از ویژگی‌های پیشرفته باید بله یا خیر باشد',
        ];
    }
} 