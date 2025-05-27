<?php

namespace App\Http\Requests\V1\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class RequestWithdrawRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:10000', 'max:10000000'],
            'bank_card_id' => ['required', 'exists:user_bank_cards,id,user_id,' . auth()->id()]
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
            'amount.required' => 'مبلغ برداشت الزامی است',
            'amount.numeric' => 'مبلغ برداشت باید عددی باشد',
            'amount.min' => 'حداقل مبلغ برداشت ۱۰,۰۰۰ تومان است',
            'amount.max' => 'حداکثر مبلغ برداشت ۱۰,۰۰۰,۰۰۰ تومان است',
            'bank_card_id.required' => 'انتخاب کارت بانکی الزامی است',
            'bank_card_id.exists' => 'کارت بانکی انتخاب شده نامعتبر است'
        ];
    }
} 