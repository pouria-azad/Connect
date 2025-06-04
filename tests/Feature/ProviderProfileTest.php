<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_own_provider_profile()
    {
        $user = User::factory()->provider()->create();
        $provider = Provider::factory()->create([
            'user_id' => $user->id,
            'provider_type' => 'senfi',
            'bio' => 'Test bio',
            'is_verified' => false,
            'profile_image' => null
        ]);
        
        $provider->senfi()->create([
            'provider_id' => $provider->id,
            'business_name' => 'Test Business',
            'business_license_number' => 'SF1234',
            'tax_id' => '1234567890',
            'business_address' => 'Test Address',
            'business_phone' => '1234567890',
            'business_hours' => [
                'monday' => [['open' => '09:00', 'close' => '17:00']],
                'tuesday' => [['open' => '09:00', 'close' => '17:00']],
                'wednesday' => [['open' => '09:00', 'close' => '17:00']],
                'thursday' => [['open' => '09:00', 'close' => '17:00']],
                'friday' => [['open' => '09:00', 'close' => '17:00']],
            ],
            'accepted_payment_methods' => ['cash', 'card'],
            'has_physical_store' => true,
            'portfolio_images' => [],
            'tags' => [],
            'base_service_fee' => 100,
            'is_verified' => false,
        ]);
        
        $this->actingAs($user);
        $response = $this->getJson('/api/v1/provider/profile');
        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $provider->id,
                'provider_type' => 'senfi',
                'bio' => 'Test bio',
                'is_verified' => false,
                'profile_image' => null
            ])
            ->assertJsonStructure([
                'id',
                'provider_type',
                'bio',
                'is_verified',
                'profile_image',
                'created_at',
                'updated_at',
                'senfi' => [
                    'provider_id',
                    'business_name',
                    'business_license_number',
                    'tax_id',
                    'business_address',
                    'business_phone',
                    'business_hours',
                    'accepted_payment_methods',
                    'has_physical_store',
                    'portfolio_images',
                    'tags',
                    'base_service_fee',
                    'is_verified'
                ]
            ]);
    }

    public function test_user_gets_404_if_no_provider_profile()
    {
        $user = User::factory()->provider()->create();
        $this->actingAs($user);
        $response = $this->getJson('/api/v1/provider/profile');
        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'پروفایل ارائه‌دهنده یافت نشد']);
    }
} 