<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Auth\Access\HandlesAuthorization;

class WalletPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user instanceof Admin;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Wallet $wallet): bool
    {
        return $user instanceof Admin || $user->id === $wallet->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // همه کاربران می‌توانند کیف پول ایجاد کنند
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Wallet $wallet): bool
    {
        return $user instanceof Admin || $user->id === $wallet->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Wallet $wallet): bool
    {
        return $user instanceof Admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Wallet $wallet): bool
    {
        return $user instanceof Admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Wallet $wallet): bool
    {
        return $user instanceof Admin;
    }

    /**
     * Determine whether the user can manage wallet transactions.
     */
    public function manageTransactions(User $user, Wallet $wallet): bool
    {
        return $user instanceof Admin || $user->id === $wallet->user_id;
    }

    /**
     * Determine whether the user can manage wallet balance.
     */
    public function manageBalance(User $user, Wallet $wallet): bool
    {
        return $user instanceof Admin || $user->id === $wallet->user_id;
    }

    /**
     * Determine whether the user can view wallet statistics.
     */
    public function viewStatistics(User $user): bool
    {
        return $user instanceof Admin;
    }

    /**
     * Determine whether the user can manage gift cards.
     */
    public function manageGiftCards(User $user, Wallet $wallet): bool
    {
        return $user instanceof Admin || $user->id === $wallet->user_id;
    }
} 