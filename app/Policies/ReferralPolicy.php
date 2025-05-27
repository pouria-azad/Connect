<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Referral;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReferralPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Referral $referral): bool
    {
        return $user->is_admin || $user->id === $referral->referrer_id || $user->id === $referral->referred_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // همه کاربران می‌توانند ارجاع ایجاد کنند
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Referral $referral): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Referral $referral): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Referral $referral): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Referral $referral): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage referral rewards.
     */
    public function manageRewards(User $user, Referral $referral): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage referral status.
     */
    public function manageStatus(User $user, Referral $referral): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can view referral statistics.
     */
    public function viewStatistics(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage referral codes.
     */
    public function manageCodes(User $user): bool
    {
        return $user->is_admin;
    }
} 