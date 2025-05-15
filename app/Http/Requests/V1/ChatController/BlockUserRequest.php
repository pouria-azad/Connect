<?php

namespace App\Http\Requests\V1\ChatController;

use Illuminate\Foundation\Http\FormRequest;


/**
 * @OA\Schema(
 *     schema="BlockUserRequest",
 *     required={"conversation_id", "user_id"},
 *     @OA\Property(
 *         property="conversation_id",
 *         type="integer",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         example=5
 *     )
 * )
 */
class BlockUserRequest extends FormRequest
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
            'conversation_id' => ['required','integer','exists:conversations,id'],
            'user_id'         => ['required','integer','exists:users,id'],
        ];
    }
}
