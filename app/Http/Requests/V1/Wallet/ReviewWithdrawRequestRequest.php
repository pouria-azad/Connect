<?php

namespace App\Http\Requests\V1\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class ReviewWithdrawRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('isAdmin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:approved,rejected'],
            'admin_note' => ['required_if:status,rejected', 'nullable', 'string', 'max:1000']
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
            'status.required' => 'وضعیت درخواست الزامی است',
            'status.in' => 'وضعیت درخواست نامعتبر است',
            'admin_note.required_if' => 'در صورت رد درخواست، توضیحات الزامی است',
            'admin_note.max' => 'حداکثر طول توضیحات ۱۰۰۰ کاراکتر است'
        ];
    }
} 