<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\WalletWithdrawRequest;
use Illuminate\Auth\Access\Response;

class WalletWithdrawRequestPolicy
{
    public function review(User $user, WalletWithdrawRequest $withdraw)
    {
        return $user->hasRole(UserRole::Admin);
    }
}
