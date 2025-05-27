<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Review;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReviewPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // همه کاربران می‌توانند لیست نظرات را ببینند
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Review $review): bool
    {
        return true; // همه کاربران می‌توانند جزئیات نظر را ببینند
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // همه کاربران می‌توانند نظر ایجاد کنند
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Review $review): bool
    {
        return $user->is_admin || $user->id === $review->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Review $review): bool
    {
        return $user->is_admin || $user->id === $review->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Review $review): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Review $review): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage review status.
     */
    public function manageStatus(User $user, Review $review): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage review reports.
     */
    public function manageReports(User $user, Review $review): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can view review statistics.
     */
    public function viewStatistics(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage review responses.
     */
    public function manageResponses(User $user, Review $review): bool
    {
        return $user->is_admin || $user->id === $review->reviewable->user_id;
    }
} 