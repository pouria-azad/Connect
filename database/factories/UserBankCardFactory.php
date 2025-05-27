<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserBankCard;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserBankCardFactory extends Factory
{
    protected $model = UserBankCard::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'card_number' => $this->faker->creditCardNumber,
            'bank_name' => $this->faker->company,
            'account_number' => $this->faker->numerify('##########'),
            'sheba_number' => 'IR' . $this->faker->numerify('##########################'),
            'is_default' => false
        ];
    }

    public function default(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_default' => true
            ];
        });
    }
} 