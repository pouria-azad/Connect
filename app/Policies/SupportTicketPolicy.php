<?php

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SupportTicketPolicy
{
    /**
     * تعیین اینکه کاربر می‌تونه تیکت رو ببینه یا نه
     */
    public function view(User $user, SupportTicket $ticket): bool
    {
        // کاربر فقط تیکت خودش رو ببینه - ادمین‌ها از جای دیگه مدیریت می‌شن
        return $user->id === $ticket->user_id;
    }

    /**
     * فقط برای استفاده ادمین‌ها از کنترلر‌های خودشونه - نه از طریق این Policy
     */
    public function update(User $user, SupportTicket $ticket): bool
    {
        return false; // فقط ادمین، نه از این Policy
    }

    public function delete(User $user, SupportTicket $ticket): bool
    {
        return false; // فقط ادمین، نه از این Policy
    }

    public function viewAny(User $user): bool
    {
        return false; // فقط ادمین، نه از این Policy
    }

    public function replyAdmin($user)
    {
        return $user instanceof \App\Models\User && $user->is_admin;
    }


}
