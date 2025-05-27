<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserBankCard;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletWithdrawRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'bank_card_id' => UserBankCard::factory(),
            'amount' => $this->faker->numberBetween(10000, 1000000),
            'status' => 'pending',
            'admin_note' => null,
            'reviewed_by_user_id' => null,
            'reviewed_at' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
                'reviewed_by_user_id' => User::factory(),
                'reviewed_at' => now(),
                'admin_note' => 'درخواست تایید شد',
            ];
        });
    }

    public function rejected(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'rejected',
                'reviewed_by_user_id' => User::factory(),
                'reviewed_at' => now(),
                'admin_note' => 'درخواست به دلیل نقص مدارک رد شد',
            ];
        });
    }
} 