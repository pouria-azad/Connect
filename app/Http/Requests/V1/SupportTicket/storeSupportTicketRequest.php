<?php

namespace App\Http\Requests\V1\SupportTicket;

use Illuminate\Foundation\Http\FormRequest;

class storeSupportTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subject' => 'required|string|max:255',
            'status' => 'nullable|in:open,answered,closed',
        ];
    }
}
