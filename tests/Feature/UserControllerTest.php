<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_user_can_get_own_profile()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/v1/user/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'user' => [
                        'id',
                        'full_name',
                        'mobile_number',
                        'username',
                        'referral_code',
                        'created_at',
                        'updated_at'
                    ],
                    'wallet'
                ]
            ]);
    }

    public function test_user_cannot_get_profile_without_auth()
    {
        $response = $this->getJson('/api/v1/user/profile');

        $response->assertStatus(401);
    }

    public function test_user_can_update_own_profile()
    {
        $response = $this->actingAsUser()
            ->putJson('/api/v1/user/profile', [
                'full_name' => 'Updated Name',
                'username' => 'updatedusername'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'پروفایل با موفقیت بروزرسانی شد'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'full_name' => 'Updated Name',
            'username' => 'updatedusername'
        ]);
    }

    public function test_user_cannot_update_profile_with_invalid_data()
    {
        $response = $this->actingAsUser()
            ->putJson('/api/v1/user/profile', [
                'username' => 'a' // Too short
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username']);
    }

    public function test_user_cannot_use_duplicate_username()
    {
        // Create another user with a specific username
        User::factory()->create(['username' => 'existinguser']);

        $response = $this->actingAsUser()
            ->putJson('/api/v1/user/profile', [
                'username' => 'existinguser'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username']);
    }

    public function test_user_can_change_password()
    {
        $response = $this->actingAsUser()
            ->putJson('/api/v1/user/change-password', [
                'current_password' => 'password',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'رمز عبور با موفقیت تغییر کرد'
            ]);
    }

    public function test_user_cannot_change_password_with_wrong_current_password()
    {
        $response = $this->actingAsUser()
            ->putJson('/api/v1/user/change-password', [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123'
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'رمز عبور فعلی اشتباه است'
            ]);
    }

    public function test_user_can_get_referral_stats()
    {
        // Create some referred users
        User::factory()->count(3)->create([
            'referred_by_user_id' => $this->user->id
        ]);

        // Create some referral transactions
        WalletTransaction::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'type' => 'referral',
            'amount' => 1000
        ]);

        $response = $this->actingAsUser()
            ->getJson('/api/v1/user/referral-stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_referrals',
                'total_earnings',
                'referral_code'
            ])
            ->assertJson([
                'total_referrals' => 3,
                'total_earnings' => 3000
            ]);
    }

    public function test_user_can_get_referral_history()
    {
        // Create some referred users
        $referredUsers = User::factory()->count(3)->create([
            'referred_by_user_id' => $this->user->id
        ]);

        // Create referral transactions
        foreach ($referredUsers as $referredUser) {
            WalletTransaction::factory()->create([
                'user_id' => $this->user->id,
                'type' => 'referral',
                'amount' => 1000,
                'related_user_id' => $referredUser->id
            ]);
        }

        $response = $this->actingAsUser()
            ->getJson('/api/v1/user/referral-history');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'amount',
                        'type',
                        'created_at'
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data');
    }
} 