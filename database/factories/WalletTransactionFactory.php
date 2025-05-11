<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WalletTransaction>
 */
class WalletTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['deposit', 'withdraw', 'transfer_in', 'transfer_out'];
        $type = $this->faker->randomElement($types);

        return [
            'user_id' => User::factory()->create()->id,
            'amount' => $this->faker->numberBetween(10, 1000) * ($type === 'withdraw' || $type === 'transfer_out' ? -1 : 1),
            'type' => $type,
            'description' => $this->faker->sentence(),
            'related_user_id' => $this->faker->boolean() ? User::factory()->create()->id : null,
        ];
    }
}
