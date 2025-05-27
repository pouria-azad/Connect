<?php

namespace App\Http\Requests\V1\Review;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_request_id' => 'required|exists:service_requests,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
            'rating_details' => 'required|array',
            'rating_details.quality' => 'required|integer|min:1|max:5',
            'rating_details.professionalism' => 'required|integer|min:1|max:5',
            'rating_details.punctuality' => 'required|integer|min:1|max:5',
            'rating_details.communication' => 'required|integer|min:1|max:5',
        ];
    }

    public function messages(): array
    {
        return [
            'service_request_id.required' => 'شناسه درخواست سرویس الزامی است',
            'service_request_id.exists' => 'درخواست سرویس مورد نظر یافت نشد',
            'rating.required' => 'امتیاز کلی الزامی است',
            'rating.integer' => 'امتیاز باید عدد صحیح باشد',
            'rating.min' => 'امتیاز باید بین 1 تا 5 باشد',
            'rating.max' => 'امتیاز باید بین 1 تا 5 باشد',
            'comment.required' => 'نظر الزامی است',
            'comment.string' => 'نظر باید متن باشد',
            'comment.max' => 'نظر نمی‌تواند بیشتر از 1000 کاراکتر باشد',
            'rating_details.required' => 'جزئیات امتیازدهی الزامی است',
            'rating_details.array' => 'فرمت جزئیات امتیازدهی نامعتبر است',
            'rating_details.quality.required' => 'امتیاز کیفیت کار الزامی است',
            'rating_details.professionalism.required' => 'امتیاز تخصص و حرفه‌ای‌گری الزامی است',
            'rating_details.punctuality.required' => 'امتیاز وقت‌شناسی الزامی است',
            'rating_details.communication.required' => 'امتیاز ارتباط و تعامل الزامی است',
        ];
    }
} 