<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotificationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // همه کاربران می‌توانند اعلان‌های خود را ببینند
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Notification $notification): bool
    {
        return $user->id === $notification->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_admin; // فقط ادمین‌ها می‌توانند اعلان ایجاد کنند
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Notification $notification): bool
    {
        return $user->id === $notification->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Notification $notification): bool
    {
        return $user->id === $notification->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Notification $notification): bool
    {
        return $user->id === $notification->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Notification $notification): bool
    {
        return $user->id === $notification->user_id;
    }

    /**
     * Determine whether the user can manage notification settings.
     */
    public function manageSettings(User $user): bool
    {
        return true; // همه کاربران می‌توانند تنظیمات اعلان‌های خود را مدیریت کنند
    }

    /**
     * Determine whether the user can view notification statistics.
     */
    public function viewStatistics(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can send bulk notifications.
     */
    public function sendBulkNotifications(User $user): bool
    {
        return $user->is_admin;
    }
} 