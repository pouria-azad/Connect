<?php

namespace Tests\Feature;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_otp()
    {
        $response = $this->postJson('/api/v1/auth/send-otp', [
            'mobile_number' => '09123456789'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'code'
            ]);

        $this->assertDatabaseHas('otp_codes', [
            'mobile_number' => '09123456789'
        ]);
    }

    public function test_user_can_verify_otp()
    {
        $otp = OtpCode::create([
            'mobile_number' => '09123456789',
            'code' => '123456',
            'expires_at' => now()->addMinutes(2),
            'is_used' => false
        ]);

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'mobile_number' => '09123456789',
            'code' => '123456'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'is_new_user'
            ]);

        $this->assertDatabaseHas('otp_codes', [
            'id' => $otp->id,
            'is_used' => true
        ]);
    }

    public function test_user_cannot_verify_expired_otp()
    {
        $otp = OtpCode::create([
            'mobile_number' => '09123456789',
            'code' => '123456',
            'expires_at' => now()->subMinutes(1),
            'is_used' => false
        ]);

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'mobile_number' => '09123456789',
            'code' => '123456'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'کد تأیید نامعتبر است'
            ]);
    }

    public function test_user_cannot_verify_used_otp()
    {
        $otp = OtpCode::create([
            'mobile_number' => '09123456789',
            'code' => '123456',
            'expires_at' => now()->addMinutes(2),
            'is_used' => true
        ]);

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'mobile_number' => '09123456789',
            'code' => '123456'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'کد تأیید نامعتبر است'
            ]);
    }

    public function test_existing_user_can_login_with_valid_otp()
    {
        $user = User::factory()->create([
            'mobile_number' => '09123456789'
        ]);

        $otp = OtpCode::create([
            'mobile_number' => '09123456789',
            'code' => '123456',
            'expires_at' => now()->addMinutes(2),
            'is_used' => false
        ]);

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'mobile_number' => '09123456789',
            'code' => '123456'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'user'
            ]);
    }

    public function test_user_can_register_with_valid_otp()
    {
        // First verify OTP
        $otp = OtpCode::create([
            'mobile_number' => '09123456789',
            'code' => '123456',
            'expires_at' => now()->addMinutes(2),
            'is_used' => false
        ]);

        $this->postJson('/api/v1/auth/verify-otp', [
            'mobile_number' => '09123456789',
            'code' => '123456'
        ]);

        // Then register
        $response = $this->postJson('/api/v1/auth/register', [
            'full_name' => 'John Doe',
            'mobile_number' => '09123456789',
            'username' => 'johndoe',
            'national_code' => '1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'user',
                'gift_card' => [
                    'code',
                    'current_balance',
                    'expires_at'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'mobile_number' => '09123456789',
            'username' => 'johndoe'
        ]);
    }

    public function test_user_cannot_register_without_verifying_otp()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'full_name' => 'John Doe',
            'mobile_number' => '09123456789',
            'username' => 'johndoe',
            'national_code' => '1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'شماره تأیید نشده است'
            ]);
    }

    public function test_user_can_register_with_referral_code()
    {
        // Create referrer user
        $referrer = User::factory()->create();

        // First verify OTP
        $otp = OtpCode::create([
            'mobile_number' => '09123456789',
            'code' => '123456',
            'expires_at' => now()->addMinutes(2),
            'is_used' => false
        ]);

        $this->postJson('/api/v1/auth/verify-otp', [
            'mobile_number' => '09123456789',
            'code' => '123456'
        ]);

        // Then register with referral code
        $response = $this->postJson('/api/v1/auth/register', [
            'full_name' => 'John Doe',
            'mobile_number' => '09123456789',
            'username' => 'johndoe',
            'national_code' => '1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'referral_code' => $referrer->referral_code
        ]);

        dump($response->json());
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'user',
                'gift_card'
            ]);

        $this->assertDatabaseHas('users', [
            'mobile_number' => '09123456789',
            'referred_by_user_id' => $referrer->id
        ]);

        // Check if referral bonus was added
        $this->assertDatabaseHas('gift_cards', [
            'assigned_to_user_id' => $referrer->id,
            'current_balance' => 5000
        ]);
    }
} 