<?php

namespace Database\Factories;

use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceRequestFactory extends Factory
{
    protected $model = ServiceRequest::class;

    public function definition(): array
    {
        return [
            'customer_user_id' => \App\Models\User::factory(),
            'service_provider_user_id' => \App\Models\User::factory(),
            'status' => 'pending_payment',
            'subject' => $this->faker->sentence(),
            'description' => $this->faker->sentence(),
            'request_type' => $this->faker->randomElement(['private', 'public']),
            'initial_fee_amount' => 15000.00,
        ];
    }
} 