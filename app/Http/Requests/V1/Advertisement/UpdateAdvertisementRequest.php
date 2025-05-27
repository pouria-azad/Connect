<?php

namespace App\Http\Requests\V1\Advertisement;

use Illuminate\Foundation\Http\FormRequest;

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