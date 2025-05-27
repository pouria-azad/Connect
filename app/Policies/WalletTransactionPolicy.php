<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Auth\Access\HandlesAuthorization;

class WalletTransactionPolicy
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
    public function view(User $user, WalletTransaction $walletTransaction): bool
    {
        return $user->is_admin || $user->id === $walletTransaction->wallet->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // همه کاربران می‌توانند تراکنش ایجاد کنند
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WalletTransaction $walletTransaction): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WalletTransaction $walletTransaction): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, WalletTransaction $walletTransaction): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, WalletTransaction $walletTransaction): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage transaction status.
     */
    public function manageStatus(User $user, WalletTransaction $walletTransaction): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage transaction refunds.
     */
    public function manageRefunds(User $user, WalletTransaction $walletTransaction): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can view transaction statistics.
     */
    public function viewStatistics(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can manage transaction disputes.
     */
    public function manageDisputes(User $user, WalletTransaction $walletTransaction): bool
    {
        return $user->is_admin || $user->id === $walletTransaction->wallet->user_id;
    }
} 