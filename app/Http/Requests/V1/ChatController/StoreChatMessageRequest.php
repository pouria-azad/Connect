<?php

namespace App\Http\Requests\V1\ChatController;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreChatMessageRequest",
 *     type="object",
 *     required={"conversation_id", "message"},
 *     @OA\Property(property="conversation_id", type="integer", example=1),
 *     @OA\Property(property="message", type="string", example="سلام، خوبی؟")
 * )
 */

class StoreChatMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $conversationId = $this->input('conversation_id');

        $isParticipant = $user->conversations()
            ->where('conversations.id', $conversationId)
            ->exists();

        return $isParticipant;
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
            'content'         => ['required','string'],
            'type'            => ['sometimes','in:text,system,invoice,payment'],
        ];
    }
}
