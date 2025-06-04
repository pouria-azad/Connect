<?php

namespace App\Http\Requests\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterSenfiProviderRequest extends FormRequest
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
            'business_name' => ['required', 'string', 'max:255'],
            'business_license_number' => ['required', 'string', 'unique:provider_senfi,business_license_number'],
            'tax_id' => ['required', 'string', 'unique:provider_senfi,tax_id'],
            'business_address' => ['required', 'string'],
            'business_phone' => ['required', 'string'],
            'business_hours' => ['required', 'array'],
            'business_hours.*' => ['array'],
            'business_hours.*.open' => ['required', 'string', 'date_format:H:i'],
            'business_hours.*.close' => ['required', 'string', 'date_format:H:i'],
            'accepted_payment_methods' => ['required', 'array'],
            'accepted_payment_methods.*' => ['string', 'in:cash,online,pos'],
            'has_physical_store' => ['required', 'boolean'],
            'province_id' => ['required', 'exists:provinces,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'portfolio_images' => ['nullable', 'array'],
            'portfolio_images.*' => ['string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
            'base_service_fee' => ['required', 'numeric', 'min:0']
        ];
    }

    public function messages(): array
    {
        return [
            'mobile_number.required' => 'شماره موبایل الزامی است',
            'mobile_number.size' => 'شماره موبایل باید ۱۱ رقم باشد',
            'mobile_number.unique' => 'این شماره موبایل قبلاً ثبت شده است',
            'password.required' => 'رمز عبور الزامی است',
            'password.min' => 'رمز عبور باید حداقل ۸ کاراکتر باشد',
            'password.confirmed' => 'تأیید رمز عبور با رمز عبور مطابقت ندارد',
            'full_name.required' => 'نام کامل الزامی است',
            'national_code.required' => 'کد ملی الزامی است',
            'national_code.size' => 'کد ملی باید ۱۰ رقم باشد',
            'national_code.unique' => 'این کد ملی قبلاً ثبت شده است',
            'business_name.required' => 'نام کسب‌وکار الزامی است',
            'business_license_number.required' => 'شماره جواز کسب الزامی است',
            'business_license_number.unique' => 'این شماره جواز کسب قبلاً ثبت شده است',
            'tax_id.required' => 'شناسه مالیاتی الزامی است',
            'tax_id.unique' => 'این شناسه مالیاتی قبلاً ثبت شده است',
            'business_address.required' => 'آدرس کسب‌وکار الزامی است',
            'business_phone.required' => 'شماره تلفن کسب‌وکار الزامی است',
            'business_hours.required' => 'ساعات کاری الزامی است',
            'accepted_payment_methods.required' => 'روش‌های پرداخت الزامی است',
            'province_id.required' => 'استان الزامی است',
            'city_id.required' => 'شهر الزامی است',
            'base_service_fee.required' => 'هزینه پایه خدمات الزامی است',
            'base_service_fee.min' => 'هزینه پایه خدمات نمی‌تواند منفی باشد'
        ];
    }
} 