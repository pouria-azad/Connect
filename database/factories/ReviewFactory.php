<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\User;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'service_request_id' => ServiceRequest::factory(),
            'customer_user_id' => User::factory(),
            'service_provider_user_id' => User::factory(),
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->sentence(),
            'rating_details' => null,
            'is_verified' => false,
            'is_visible' => true,
        ];
    }
} 