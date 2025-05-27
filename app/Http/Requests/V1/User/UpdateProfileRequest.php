<?php

namespace App\Http\Requests\V1\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'username' => ['sometimes', 'string', 'min:3', 'max:50', 'unique:users,username,' . $this->user()->id],
            'full_name' => ['sometimes', 'string', 'max:255'],
            'national_id' => ['sometimes', 'string', 'size:10', 'unique:users,national_id,' . $this->user()->id],
            'password' => ['sometimes', 'string', 'min:6'],
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
            'username.required' => 'نام کاربری الزامی است',
            'username.min' => 'نام کاربری باید حداقل 3 کاراکتر باشد',
            'username.max' => 'نام کاربری نمی‌تواند بیشتر از 50 کاراکتر باشد',
            'username.unique' => 'این نام کاربری قبلا ثبت شده است',
            'full_name.required' => 'نام و نام خانوادگی الزامی است',
            'full_name.max' => 'نام و نام خانوادگی نمی‌تواند بیشتر از 255 کاراکتر باشد',
            'national_id.required' => 'کد ملی الزامی است',
            'national_id.size' => 'کد ملی باید 10 رقم باشد',
            'national_id.unique' => 'این کد ملی قبلا ثبت شده است',
            'password.required' => 'رمز عبور الزامی است',
            'password.min' => 'رمز عبور باید حداقل 6 کاراکتر باشد',
        ];
    }
} 