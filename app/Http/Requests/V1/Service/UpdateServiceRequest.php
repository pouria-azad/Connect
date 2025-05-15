<?php

namespace App\Http\Requests\V1\Service;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UpdateServiceRequest",
 *     type="object",
 *     @OA\Property(property="title", type="string", example="Web Development", description="Title of the service, optional"),
 *     @OA\Property(property="description", type="string", example="Updated web development services", description="Description of the service, optional"),
 *     @OA\Property(property="category_id", type="integer", example=1, description="ID of the service category, optional"),
 *     @OA\Property(property="parent_id", type="integer", example=1, description="ID of the parent service, optional")
 * )
 */
class UpdateServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled in the controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string|max:1000',
            'category_id' => 'sometimes|required|exists:service_categories,id',
            'parent_id'   => 'nullable|exists:services,id',
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
            'title.required'       => 'عنوان سرویس الزامی است.',
            'title.string'         => 'عنوان سرویس باید متنی باشد.',
            'title.max'            => 'عنوان سرویس نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'description.string'    => 'توضیحات سرویس باید متنی باشد.',
            'description.max'      => 'توضیحات سرویس نمی‌تواند بیشتر از ۱۰۰۰ کاراکتر باشد.',
            'category_id.required' => 'شناسه دسته‌بندی الزامی است.',
            'category_id.exists'   => 'دسته‌بندی انتخاب‌شده معتبر نیست.',
            'parent_id.exists'     => 'سرویس والد انتخاب‌شده معتبر نیست.',
        ];
    }
}
