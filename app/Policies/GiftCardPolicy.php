<?php

namespace App\Policies;

use App\Models\User;
use App\Models\GiftCard;
use Illuminate\Auth\Access\HandlesAuthorization;

class GiftCardPolicy
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
    public function view(User $user, GiftCard $giftCard): bool
    {
        return $user->is_admin || $user->id === $giftCard->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_admin; // فقط ادمین‌ها می‌توانند کارت هدیه ایجاد کنند
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GiftCard $giftCard): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GiftCard $giftCard): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, GiftCard $giftCard): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, GiftCard $giftCard): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage gift card status.
     */
    public function manageStatus(User $user, GiftCard $giftCard): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage gift card balance.
     */
    public function manageBalance(User $user, GiftCard $giftCard): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can view gift card statistics.
     */
    public function viewStatistics(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage gift card usage.
     */
    public function manageUsage(User $user, GiftCard $giftCard): bool
    {
        return $user->is_admin || $user->id === $giftCard->user_id;
    }
} 