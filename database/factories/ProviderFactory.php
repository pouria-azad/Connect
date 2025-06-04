<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Provider>
 */
class ProviderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $providerType = fake()->randomElement(['senfi', 'canctyar']);
        return [
            'user_id' => User::factory(),
            'provider_type' => $providerType,
            'bio' => fake()->paragraph(),
            'is_verified' => fake()->boolean(20), // 20% شانس تایید بودن
            'profile_image' => fake()->optional()->imageUrl(200, 200, 'people'),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function senfi(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'provider_type' => 'senfi',
            ];
        })->afterCreating(function ($provider) {
            $provider->senfi()->create([
                'provider_id' => $provider->id,
                'business_name' => fake()->company(),
                'business_license_number' => fake()->unique()->numerify('SF####'),
                'tax_id' => fake()->unique()->numerify('##########'),
                'business_address' => fake()->address(),
                'business_phone' => fake()->phoneNumber(),
                'business_hours' => [
                    'monday' => [['open' => '09:00', 'close' => '17:00']],
                    'tuesday' => [['open' => '09:00', 'close' => '17:00']],
                    'wednesday' => [['open' => '09:00', 'close' => '17:00']],
                    'thursday' => [['open' => '09:00', 'close' => '17:00']],
                    'friday' => [['open' => '09:00', 'close' => '17:00']],
                ],
                'accepted_payment_methods' => ['cash', 'card'],
                'has_physical_store' => fake()->boolean(),
                'portfolio_images' => [],
                'tags' => [],
                'base_service_fee' => fake()->randomFloat(2, 100, 1000),
                'is_verified' => false,
            ]);
        });
    }

    public function canctyar(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'provider_type' => 'canctyar',
            ];
        })->afterCreating(function ($provider) {
            $provider->canctyar()->create([
                'provider_id' => $provider->id,
                'canctyar_number' => fake()->unique()->numerify('CT####'),
                'expertise_areas' => fake()->words(3),
            ]);
        });
    }
}
