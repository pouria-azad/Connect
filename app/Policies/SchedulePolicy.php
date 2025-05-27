<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Schedule;
use Illuminate\Auth\Access\HandlesAuthorization;

class SchedulePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin; // فقط ادمین‌ها می‌توانند لیست زمانبندی‌ها را ببینند
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Schedule $schedule): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_admin; // فقط ادمین‌ها می‌توانند زمانبندی جدید ایجاد کنند
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Schedule $schedule): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Schedule $schedule): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Schedule $schedule): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Schedule $schedule): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage schedule types.
     */
    public function manageTypes(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage schedule tasks.
     */
    public function manageTasks(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can view schedule statistics.
     */
    public function viewStatistics(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage schedule logs.
     */
    public function manageLogs(User $user): bool
    {
        return $user->is_admin;
    }
} 