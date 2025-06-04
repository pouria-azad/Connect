<?php

namespace Database\Factories;

use App\Models\Provider;
use App\Models\ProviderSenfi;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProviderSenfiFactory extends Factory
{
    protected $model = ProviderSenfi::class;

    public function definition(): array
    {
        return [
            'provider_id' => Provider::factory(),
            'business_name' => fake()->company(),
            'business_license_number' => fake()->unique()->numerify('BL-#####'),
            'tax_id' => fake()->unique()->numerify('TAX-#####'),
            'business_address' => fake()->address(),
            'business_phone' => fake()->phoneNumber(),
            'business_hours' => [
                'saturday' => ['open' => '09:00', 'close' => '19:00'],
                'sunday' => ['open' => '09:00', 'close' => '19:00'],
                'monday' => ['open' => '09:00', 'close' => '19:00'],
                'tuesday' => ['open' => '09:00', 'close' => '19:00'],
                'wednesday' => ['open' => '09:00', 'close' => '19:00'],
                'thursday' => ['open' => '09:00', 'close' => '19:00']
            ],
            'accepted_payment_methods' => ['cash', 'pos', 'online'],
            'has_physical_store' => fake()->boolean(80),
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
            'base_service_fee' => fake()->numberBetween(50000, 500000),
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
} 