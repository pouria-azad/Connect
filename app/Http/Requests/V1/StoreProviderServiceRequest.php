<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreProviderServiceRequest extends FormRequest
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
            'service_id'         => 'required|exists:services,id',
            'price'              => 'nullable|numeric|min:0',
            'custom_description' => 'nullable|string|max:500',
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
            'service_id.required' => 'شناسه سرویس الزامی است.',
            'service_id.exists'   => 'سرویس انتخاب‌شده معتبر نیست.',
            'price.numeric'       => 'قیمت باید یک عدد باشد.',
            'price.min'           => 'قیمت نمی‌تواند منفی باشد.',
            'custom_description.string' => 'توضیحات سفارشی باید متنی باشد.',
            'custom_description.max'   => 'توضیحات سفارشی نمی‌تواند بیشتر از ۵۰۰ کاراکتر باشد.',
        ];
    }
}
