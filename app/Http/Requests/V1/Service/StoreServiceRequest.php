<?php

namespace App\Http\Requests\V1\Service;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreServiceRequest",
 *     type="object",
 *     required={"title", "category_id"},
 *     @OA\Property(property="title", type="string", example="Web Development", description="Title of the service"),
 *     @OA\Property(property="description", type="string", example="Professional web development services", description="Description of the service, optional"),
 *     @OA\Property(property="category_id", type="integer", example=1, description="ID of the service category"),
 *     @OA\Property(property="parent_id", type="integer", example=1, description="ID of the parent service, optional")
 * )
 */
class StoreServiceRequest extends FormRequest
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
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category_id' => 'nullable|exists:service_categories,id',
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
