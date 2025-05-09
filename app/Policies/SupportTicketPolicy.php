<?php

namespace App\Policies;

use App\Enums\UserRole;
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
        // کاربر عادی فقط تیکت‌های خودش رو ببینه، ادمین همه تیکت‌ها
        return $user->id === $ticket->user_id || $user->hasRole(UserRole::Admin);
    }

    /**
     * تعیین اینکه کاربر می‌تونه تیکت رو ویرایش کنه
     */
    public function update(User $user, SupportTicket $ticket): bool
    {
        // فقط ادمین بتونه تیکت رو ویرایش کنه
        return $user->hasRole(UserRole::Admin);
    }

    /**
     * تعیین اینکه کاربر می‌تونه تیکت رو حذف کنه
     */
    public function delete(User $user, SupportTicket $ticket): bool
    {
        // فقط ادمین بتونه تیکت رو حذف کنه
        return $user->hasRole(UserRole::Admin);
    }

    /**
     * تعیین اینکه کاربر می‌تونه همه تیکت‌ها رو ببینه
     */
    public function viewAny(User $user): bool
    {
        // فقط ادمین بتونه همه تیکت‌ها رو ببینه
        return $user->hasRole(UserRole::Admin);
    }
}
