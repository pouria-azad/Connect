<?php

namespace App\Http\Requests\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'mobile_number' => ['required', 'string', 'regex:/^9[0-9]{9}$/', 'unique:users,mobile_number'],
            'full_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'national_code' => ['required', 'string', 'size:10', 'unique:users,national_code'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'referral_code' => ['nullable', 'string', 'exists:users,referral_code'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mobile_number.required' => 'شماره موبایل الزامی است',
            'mobile_number.regex' => 'فرمت شماره موبایل نامعتبر است',
            'mobile_number.unique' => 'این شماره موبایل قبلاً ثبت شده است',
            'full_name.required' => 'نام و نام خانوادگی الزامی است',
            'full_name.max' => 'نام و نام خانوادگی نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد',
            'username.required' => 'نام کاربری الزامی است',
            'username.unique' => 'این نام کاربری قبلاً ثبت شده است',
            'national_code.required' => 'کد ملی الزامی است',
            'national_code.size' => 'کد ملی باید ۱۰ رقم باشد',
            'national_code.unique' => 'این کد ملی قبلاً ثبت شده است',
            'password.required' => 'رمز عبور الزامی است',
            'password.min' => 'رمز عبور باید حداقل ۸ کاراکتر باشد',
            'password.confirmed' => 'تکرار رمز عبور مطابقت ندارد',
            'referral_code.exists' => 'کد معرف نامعتبر است',
        ];
    }
} 