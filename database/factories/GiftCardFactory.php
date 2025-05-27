<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GiftCardFactory extends Factory
{
    public function definition(): array
    {
        $initialBalance = $this->faker->numberBetween(10000, 1000000);
        
        return [
            'code' => strtoupper(Str::random(8)),
            'initial_balance' => $initialBalance,
            'current_balance' => $initialBalance,
            'is_used' => false,
            'is_active' => true,
            'created_by_user_id' => User::factory(),
            'used_by_user_id' => null,
            'expires_at' => now()->addDays(30),
            'redeemed_at' => null,
        ];
    }

    public function used(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_used' => true,
                'used_by_user_id' => User::factory(),
                'current_balance' => 0,
                'redeemed_at' => now(),
            ];
        });
    }

    public function expired(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'expires_at' => now()->subDay(),
            ];
        });
    }

    public function inactive(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
} 