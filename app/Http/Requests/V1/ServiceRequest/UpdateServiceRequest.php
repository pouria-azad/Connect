<?php

namespace App\Http\Requests\V1\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
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
            'subject' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|max:1000',
            'status' => 'sometimes|required|in:pending_payment,pending_admin_approval,approved_by_admin,rejected_by_admin,pending_sp_acceptance,accepted_by_sp,rejected_by_sp,canceled_by_customer,completed,expired,ready_for_review',
            'admin_notes' => 'nullable|string|max:1000',
            'rejection_reason' => 'nullable|string|max:1000',
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
            'status.required' => 'وضعیت درخواست الزامی است.',
            'status.in' => 'وضعیت درخواست نامعتبر است.',
            'admin_notes.string' => 'یادداشت‌های مدیر باید متنی باشد.',
            'admin_notes.max' => 'یادداشت‌های مدیر نمی‌تواند بیشتر از ۱۰۰۰ کاراکتر باشد.',
            'rejection_reason.string' => 'دلیل رد درخواست باید متنی باشد.',
            'rejection_reason.max' => 'دلیل رد درخواست نمی‌تواند بیشتر از ۱۰۰۰ کاراکتر باشد.',
            'files.array' => 'فرمت فایل‌ها نامعتبر است.',
            'files.*.file' => 'فایل آپلود شده نامعتبر است.',
            'files.*.max' => 'حجم هر فایل نمی‌تواند بیشتر از ۱۰ مگابایت باشد.',
            'files.*.mimes' => 'فرمت فایل مجاز نیست. فرمت‌های مجاز: jpeg, png, jpg, pdf, doc, docx, xls, xlsx, zip, rar'
        ];
    }
} 