<?php

namespace App\Http\Requests\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterCanctyarProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mobile_number' => ['required', 'string', 'regex:/^9[0-9]{9}$/', 'unique:users,mobile_number'],
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers(), 'confirmed'],
            'full_name' => ['required', 'string', 'max:255'],
            'national_code' => ['required', 'string', 'size:10', 'unique:users,national_code'],
            'certification_number' => ['required', 'string', 'unique:provider_canctyar,certification_number'],
            'skills' => ['required', 'array', 'min:1'],
            'skills.*' => ['string'],
            'service_areas' => ['required', 'array', 'min:1'],
            'service_areas.*' => ['integer', 'exists:cities,id'],
            'availability_hours' => ['required', 'array'],
            'availability_hours.*' => ['array'],
            'availability_hours.*.start' => ['required', 'string', 'date_format:H:i'],
            'availability_hours.*.end' => ['required', 'string', 'date_format:H:i'],
            'can_travel' => ['required', 'boolean'],
            'travel_fee_per_km' => ['required_if:can_travel,true', 'numeric', 'min:0'],
            'minimum_service_fee' => ['required', 'numeric', 'min:0'],
            'province_id' => ['required', 'exists:provinces,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'portfolio_images' => ['nullable', 'array'],
            'portfolio_images.*' => ['string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string']
        ];
    }

    public function messages(): array
    {
        return [
            'mobile_number.required' => 'شماره موبایل الزامی است',
            'mobile_number.regex' => 'شماره موبایل باید به صورت 9XXXXXXXXX باشد',
            'mobile_number.unique' => 'این شماره موبایل قبلاً ثبت شده است',
            'password.required' => 'رمز عبور الزامی است',
            'password.min' => 'رمز عبور باید حداقل ۸ کاراکتر باشد',
            'password.confirmed' => 'تأیید رمز عبور با رمز عبور مطابقت ندارد',
            'full_name.required' => 'نام کامل الزامی است',
            'national_code.required' => 'کد ملی الزامی است',
            'national_code.size' => 'کد ملی باید ۱۰ رقم باشد',
            'national_code.unique' => 'این کد ملی قبلاً ثبت شده است',
            'certification_number.required' => 'شماره گواهینامه الزامی است',
            'certification_number.unique' => 'این شماره گواهینامه قبلاً ثبت شده است',
            'skills.required' => 'مهارت‌ها الزامی است',
            'skills.min' => 'حداقل یک مهارت باید وارد شود',
            'service_areas.required' => 'مناطق سرویس‌دهی الزامی است',
            'service_areas.min' => 'حداقل یک منطقه سرویس‌دهی باید انتخاب شود',
            'availability_hours.required' => 'ساعات در دسترس بودن الزامی است',
            'can_travel.required' => 'وضعیت امکان سفر باید مشخص شود',
            'travel_fee_per_km.required_if' => 'هزینه سفر به ازای هر کیلومتر الزامی است',
            'travel_fee_per_km.min' => 'هزینه سفر نمی‌تواند منفی باشد',
            'minimum_service_fee.required' => 'حداقل هزینه خدمات الزامی است',
            'minimum_service_fee.min' => 'حداقل هزینه خدمات نمی‌تواند منفی باشد',
            'province_id.required' => 'استان الزامی است',
            'city_id.required' => 'شهر الزامی است'
        ];
    }
} 