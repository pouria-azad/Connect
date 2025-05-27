<?php

namespace App\Http\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdvertisementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image_url' => ['sometimes', 'required', 'url', 'max:255'],
            'target_url' => ['sometimes', 'required', 'url', 'max:255'],
            'type' => ['sometimes', 'required', 'string', 'in:banner,popup,sidebar'],
            'position' => ['nullable', 'string', 'in:top,bottom,left,right'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'status' => ['sometimes', 'required', 'string', 'in:active,inactive'],
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
            'title.required' => 'عنوان تبلیغ الزامی است.',
            'image_url.required' => 'آدرس تصویر تبلیغ الزامی است.',
            'image_url.url' => 'آدرس تصویر تبلیغ باید یک URL معتبر باشد.',
            'target_url.required' => 'آدرس مقصد تبلیغ الزامی است.',
            'target_url.url' => 'آدرس مقصد تبلیغ باید یک URL معتبر باشد.',
            'type.required' => 'نوع تبلیغ الزامی است.',
            'type.in' => 'نوع تبلیغ باید یکی از مقادیر banner، popup یا sidebar باشد.',
            'position.in' => 'موقعیت تبلیغ باید یکی از مقادیر top، bottom، left یا right باشد.',
            'end_date.after' => 'تاریخ پایان باید بعد از تاریخ شروع باشد.',
            'status.required' => 'وضعیت تبلیغ الزامی است.',
            'status.in' => 'وضعیت تبلیغ باید active یا inactive باشد.',
        ];
    }
} 