<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WalletTransaction>
 */
class WalletTransactionFactory extends Factory
{
    protected $model = WalletTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'wallet_id' => Wallet::factory(),
            'amount' => $this->faker->numberBetween(1000, 100000),
            'type' => $this->faker->randomElement(['deposit', 'withdraw', 'transfer_in', 'transfer_out', 'referral']),
            'description' => $this->faker->sentence(),
            'related_user_id' => null,
            'status' => 'completed'
        ];
    }
}
