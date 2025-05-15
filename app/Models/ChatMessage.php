<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ChatMessage",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="conversation_id", type="integer", example=1),
 *     @OA\Property(property="sender_id", type="integer", example=3),
 *     @OA\Property(property="content", type="string", example="سلام!"),
 *     @OA\Property(property="type", type="string", enum={"text","system","invoice","payment"}, example="text"),
 *     @OA\Property(property="is_read", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 * )
 */

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id', 'sender_id', 'content', 'type', 'is_read'
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
