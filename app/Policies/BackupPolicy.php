<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Backup;
use Illuminate\Auth\Access\HandlesAuthorization;

class BackupPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin; // فقط ادمین‌ها می‌توانند لیست پشتیبان‌ها را ببینند
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Backup $backup): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_admin; // فقط ادمین‌ها می‌توانند پشتیبان جدید ایجاد کنند
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Backup $backup): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Backup $backup): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Backup $backup): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Backup $backup): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage backup types.
     */
    public function manageTypes(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage backup storage.
     */
    public function manageStorage(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can view backup statistics.
     */
    public function viewStatistics(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage backup schedules.
     */
    public function manageSchedules(User $user): bool
    {
        return $user->is_admin;
    }
} 