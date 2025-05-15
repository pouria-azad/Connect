<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ChatMessageResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=10),
 *     @OA\Property(property="conversation_id", type="integer", example=1),
 *     @OA\Property(property="sender_id", type="integer", example=5),
 *     @OA\Property(property="message", type="string", example="سلام"),
 *     @OA\Property(property="is_read", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

class ChatMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender_id' => $this->sender_id,
            'sender' => [
                'id' => $this->sender->id,
                'name' => $this->sender->name,
            ],
            'content' => $this->content,
            'type' => $this->type,
            'is_read' => (bool) $this->is_read,
            'sent_at' => $this->created_at->toIso8601String(),
        ];
    }
}
