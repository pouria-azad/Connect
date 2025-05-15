<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ChatMessage;
use App\Models\Conversation;

class ChatMessagePolicy
{
    /**
     * فقط اعضای مکالمه‌ای که بلاک نشده‌ان، می‌تونن پیام ارسال کنن
     */
    public function create(User $user, Conversation $conversation): bool
    {
        $pivot = $conversation->users()
            ->where('user_id', $user->id)
            ->first()?->pivot;

        return $pivot && !$pivot->is_blocked;
    }

    /**
     * فقط اعضا می‌تونن پیام‌ها رو ببینن
     */
    public function view(User $user, ChatMessage $chatMessage): bool
    {
        return $chatMessage->conversation
            ->users()
            ->where('user_id', $user->id)
            ->exists();
    }
}
