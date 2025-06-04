<?php

namespace Database\Factories;

use App\Models\Provider;
use App\Models\ProviderCanctyar;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProviderCanctyarFactory extends Factory
{
    protected $model = ProviderCanctyar::class;

    public function definition(): array
    {
        return [
            'provider_id' => Provider::factory(),
            'certification_number' => fake()->unique()->numerify('CERT-#####'),
            'skills' => [
                fake()->jobTitle(),
                fake()->jobTitle(),
                fake()->jobTitle()
            ],
            'service_areas' => [
                fake()->numberBetween(1, 100),
                fake()->numberBetween(1, 100)
            ],
            'availability_hours' => [
                'monday' => ['start' => '09:00', 'end' => '17:00'],
                'tuesday' => ['start' => '09:00', 'end' => '17:00'],
                'wednesday' => ['start' => '09:00', 'end' => '17:00'],
                'thursday' => ['start' => '09:00', 'end' => '17:00'],
                'friday' => ['start' => '09:00', 'end' => '13:00']
            ],
            'can_travel' => fake()->boolean(70),
            'travel_fee_per_km' => fake()->numberBetween(5000, 20000),
            'minimum_service_fee' => fake()->numberBetween(100000, 1000000),
            'portfolio_images' => [
                fake()->imageUrl(),
                fake()->imageUrl(),
                fake()->imageUrl()
            ],
            'tags' => [
                fake()->word(),
                fake()->word(),
                fake()->word()
            ],
            'is_verified' => fake()->boolean(20),
            'verified_at' => fn (array $attrs) => $attrs['is_verified'] ? fake()->dateTimeBetween('-1 year') : null,
            'verified_by_admin_id' => fn (array $attrs) => $attrs['is_verified'] ? fake()->numberBetween(1, 10) : null
        ];
    }

    public function verified(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
            'verified_at' => fake()->dateTimeBetween('-1 year'),
            'verified_by_admin_id' => fake()->numberBetween(1, 10)
        ]);
    }

    public function unverified(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => false,
            'verified_at' => null,
            'verified_by_admin_id' => null
        ]);
    }

    public function canTravel(): self
    {
        return $this->state(fn (array $attributes) => [
            'can_travel' => true,
            'travel_fee_per_km' => fake()->numberBetween(5000, 20000)
        ]);
    }

    public function cannotTravel(): self
    {
        return $this->state(fn (array $attributes) => [
            'can_travel' => false,
            'travel_fee_per_km' => 0
        ]);
    }
} 