<?php

namespace App\Http\Requests\V1\SubscriptionPlan;

use Illuminate\Foundation\Http\FormRequest;

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