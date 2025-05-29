<?php

namespace App\Http\Requests\V1\Advertisement;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateAdvertisementRequest",
 *     type="object",
 *     title="Update Advertisement Request",
 *     description="درخواست بروزرسانی تبلیغ",
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="عنوان تبلیغ",
 *         example="تبلیغ نمونه",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="توضیحات تبلیغ",
 *         example="این یک تبلیغ نمونه است"
 *     ),
 *     @OA\Property(
 *         property="image_url",
 *         type="string",
 *         description="آدرس تصویر تبلیغ",
 *         example="https://example.com/image.jpg",
 *         maxLength=2048
 *     ),
 *     @OA\Property(
 *         property="target_url",
 *         type="string",
 *         description="آدرس مقصد تبلیغ",
 *         example="https://example.com",
 *         maxLength=2048
 *     ),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         description="نوع تبلیغ",
 *         enum={"banner", "popup", "sidebar"},
 *         example="banner"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="وضعیت تبلیغ",
 *         enum={"active", "inactive"},
 *         example="active"
 *     ),
 *     @OA\Property(
 *         property="start_date",
 *         type="string",
 *         format="date",
 *         description="تاریخ شروع نمایش تبلیغ",
 *         example="2025-05-23",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="end_date",
 *         type="string",
 *         format="date",
 *         description="تاریخ پایان نمایش تبلیغ",
 *         example="2025-06-23",
 *         nullable=true
 *     )
 * )
 */
class UpdateAdvertisementRequest extends FormRequest
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
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'image_url' => ['sometimes', 'required', 'url', 'max:2048'],
            'target_url' => ['sometimes', 'required', 'url', 'max:2048'],
            'type' => ['sometimes', 'required', 'string', 'in:banner,popup,sidebar'],
            'status' => ['sometimes', 'required', 'string', 'in:active,inactive'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
} 