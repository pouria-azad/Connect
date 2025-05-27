<?php

namespace Database\Factories;

use App\Models\ProviderService;
use App\Models\Provider;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProviderServiceFactory extends Factory
{
    protected $model = ProviderService::class;

    public function definition(): array
    {
        return [
            'provider_id' => Provider::factory(),
            'service_id' => Service::factory(),
            'price' => $this->faker->numberBetween(10000, 1000000),
            'custom_description' => $this->faker->optional()->sentence(),
        ];
    }
} 