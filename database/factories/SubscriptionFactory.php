<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        $plan = SubscriptionPlan::factory()->create();
        
        return [
            'user_id' => User::factory(),
            'subscription_plan_id' => $plan->id,
            'status' => $this->faker->randomElement(['active', 'expired', 'cancelled']),
            'start_date' => now(),
            'end_date' => now()->addDays($plan->duration_days),
            'amount' => $plan->price,
            'payment_method' => $this->faker->randomElement(['wallet', 'bank_card', 'gift_card']),
            'payment_status' => $this->faker->randomElement(['pending', 'completed', 'failed']),
            'payment_id' => $this->faker->uuid,
            'auto_renew' => $this->faker->boolean,
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ];
    }

    public function active(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
                'end_date' => now()->addDays(30),
                'cancelled_at' => null,
                'cancellation_reason' => null,
            ];
        });
    }

    public function expired(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'expired',
                'end_date' => now()->subDays(1),
            ];
        });
    }

    public function cancelled(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
                'cancelled_at' => now()->subDays(5),
                'cancellation_reason' => $this->faker->sentence,
            ];
        });
    }
} 