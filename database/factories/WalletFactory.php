<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'balance' => $this->faker->numberBetween(0, 1000000)
        ];
    }

    public function empty(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'balance' => 0
            ];
        });
    }

    public function withBalance(int $balance): static
    {
        return $this->state(function (array $attributes) use ($balance) {
            return [
                'balance' => $balance
            ];
        });
    }
} 