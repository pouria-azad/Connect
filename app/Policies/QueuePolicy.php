<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Queue;
use Illuminate\Auth\Access\HandlesAuthorization;

class QueuePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin; // فقط ادمین‌ها می‌توانند لیست صف‌ها را ببینند
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Queue $queue): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_admin; // فقط ادمین‌ها می‌توانند صف جدید ایجاد کنند
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Queue $queue): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Queue $queue): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Queue $queue): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Queue $queue): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage queue types.
     */
    public function manageTypes(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage queue workers.
     */
    public function manageWorkers(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can view queue statistics.
     */
    public function viewStatistics(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage queue schedules.
     */
    public function manageSchedules(User $user): bool
    {
        return $user->is_admin;
    }
} 