<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ConversationPolicy
{
    // فقط کسانی که داخل مکالمه هستن اجازه update (block/unblock) دارن
    public function update(User $user, Conversation $conversation): bool
    {
        return $conversation->users()->where('user_id', $user->id)->exists();
    }

    // اجازه مشاهده پیام‌ها فقط برای اعضای مکالمه
    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->users()->where('user_id', $user->id)->exists();
    }
}
