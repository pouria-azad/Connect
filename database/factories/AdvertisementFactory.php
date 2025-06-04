<?php

namespace Database\Factories;

use App\Models\Advertisement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdvertisementFactory extends Factory
{
    protected $model = Advertisement::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'type' => 'banner',
            'status' => 'pending',
            'start_date' => now(),
            'end_date' => now()->addDays(10),
            'display_count' => 0,
            'click_count' => 0,
            'created_by_admin_id' => null,
            'code' => strtoupper($this->faker->unique()->bothify('AD####??')),
        ];
    }
} 