<?php

namespace Database\Factories;

use App\Models\Provider;
use App\Models\Service;
use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider_id' => \App\Models\Provider::factory(),
            'service_id' => Service::factory(),
            'total_price' => $this->faker->randomFloat(2, 50, 2000),
            'status' => $this->faker->randomElement([
                'pending', 'accepted', 'in_progress', 'completed', 'canceled', 'rejected'
            ]),
            'requirements' => $this->faker->sentence(),
            'completed_at' => $this->faker->dateTimeBetween('-1 months', '+1 days'),
        ];
    }
}
