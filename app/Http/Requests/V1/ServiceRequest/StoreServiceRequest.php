<?php

namespace App\Http\Requests\V1\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'request_type' => 'required|in:private,public',
            'service_category_id' => 'required_if:request_type,private|exists:service_categories,id',
            'service_provider_user_id' => 'required_if:request_type,private|exists:users,id',
            'province_id' => 'required_if:request_type,public|exists:provinces,id',
            'city_id' => 'required_if:request_type,public|exists:cities,id',
            'scope_type' => 'required_if:request_type,public|in:city_wide,nation_wide',
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240|mimes:jpeg,png,jpg,pdf,doc,docx,xls,xlsx,zip,rar'
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'subject.required' => 'موضوع درخواست الزامی است.',
            'subject.string' => 'موضوع درخواست باید متنی باشد.',
            'subject.max' => 'موضوع درخواست نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'description.required' => 'توضیحات درخواست الزامی است.',
            'description.string' => 'توضیحات درخواست باید متنی باشد.',
            'description.max' => 'توضیحات درخواست نمی‌تواند بیشتر از ۱۰۰۰ کاراکتر باشد.',
            'request_type.required' => 'نوع درخواست الزامی است.',
            'request_type.in' => 'نوع درخواست نامعتبر است.',
            'service_category_id.required_if' => 'دسته‌بندی سرویس برای درخواست خصوصی الزامی است.',
            'service_category_id.exists' => 'دسته‌بندی سرویس انتخاب شده معتبر نیست.',
            'service_provider_user_id.required_if' => 'خدمات دهنده برای درخواست خصوصی الزامی است.',
            'service_provider_user_id.exists' => 'خدمات دهنده انتخاب شده معتبر نیست.',
            'province_id.required_if' => 'استان برای درخواست عمومی الزامی است.',
            'province_id.exists' => 'استان انتخاب شده معتبر نیست.',
            'city_id.required_if' => 'شهر برای درخواست عمومی الزامی است.',
            'city_id.exists' => 'شهر انتخاب شده معتبر نیست.',
            'scope_type.required_if' => 'محدوده ارسال برای درخواست عمومی الزامی است.',
            'scope_type.in' => 'محدوده ارسال نامعتبر است.',
            'files.array' => 'فرمت فایل‌ها نامعتبر است.',
            'files.*.file' => 'فایل آپلود شده نامعتبر است.',
            'files.*.max' => 'حجم هر فایل نمی‌تواند بیشتر از ۱۰ مگابایت باشد.',
            'files.*.mimes' => 'فرمت فایل مجاز نیست. فرمت‌های مجاز: jpeg, png, jpg, pdf, doc, docx, xls, xlsx, zip, rar'
        ];
    }
} 