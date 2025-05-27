<?php

namespace App\Http\Requests\V1\Review;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'sometimes|string|max:1000',
            'rating_details' => 'sometimes|array',
            'rating_details.quality' => 'required_with:rating_details|integer|min:1|max:5',
            'rating_details.professionalism' => 'required_with:rating_details|integer|min:1|max:5',
            'rating_details.punctuality' => 'required_with:rating_details|integer|min:1|max:5',
            'rating_details.communication' => 'required_with:rating_details|integer|min:1|max:5',
        ];
    }

    public function messages(): array
    {
        return [
            'rating.integer' => 'امتیاز باید عدد صحیح باشد',
            'rating.min' => 'امتیاز باید بین 1 تا 5 باشد',
            'rating.max' => 'امتیاز باید بین 1 تا 5 باشد',
            'comment.string' => 'نظر باید متن باشد',
            'comment.max' => 'نظر نمی‌تواند بیشتر از 1000 کاراکتر باشد',
            'rating_details.array' => 'فرمت جزئیات امتیازدهی نامعتبر است',
            'rating_details.quality.required_with' => 'امتیاز کیفیت کار الزامی است',
            'rating_details.professionalism.required_with' => 'امتیاز تخصص و حرفه‌ای‌گری الزامی است',
            'rating_details.punctuality.required_with' => 'امتیاز وقت‌شناسی الزامی است',
            'rating_details.communication.required_with' => 'امتیاز ارتباط و تعامل الزامی است',
        ];
    }
} 