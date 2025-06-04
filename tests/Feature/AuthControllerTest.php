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
            'mobile_number' => '9123456789'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'code'
            ]);

        $this->assertDatabaseHas('otp_codes', [
            'mobile_number' => '9123456789'
        ]);
    }

    public function test_user_can_verify_otp()
    {
        $otp = OtpCode::create([
            'mobile_number' => '9123456789',
            'code' => '123456',
            'expires_at' => now()->addMinutes(2),
            'is_used' => false
        ]);

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'mobile_number' => '9123456789',
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
            'mobile_number' => '9123456789',
            'code' => '123456',
            'expires_at' => now()->subMinutes(1),
            'is_used' => false
        ]);

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'mobile_number' => '9123456789',
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
            'mobile_number' => '9123456789',
            'code' => '123456',
            'expires_at' => now()->addMinutes(2),
            'is_used' => true
        ]);

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'mobile_number' => '9123456789',
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
            'mobile_number' => '9123456789'
        ]);

        // Create wallet for user
        $user->wallet()->create([
            'balance' => 0
        ]);

        $otp = OtpCode::create([
            'mobile_number' => '9123456789',
            'code' => '123456',
            'expires_at' => now()->addMinutes(2),
            'is_used' => false
        ]);

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'mobile_number' => '9123456789',
            'code' => '123456'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'user'
            ]);

        $this->assertDatabaseHas('otp_codes', [
            'id' => $otp->id,
            'is_used' => true
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'balance' => 0
        ]);
    }

    public function test_user_can_register_with_valid_otp()
    {
        // First verify OTP
        $otp = OtpCode::create([
            'mobile_number' => '9123456789',
            'code' => '123456',
            'expires_at' => now()->addMinutes(2),
            'is_used' => false
        ]);

        $this->postJson('/api/v1/auth/verify-otp', [
            'mobile_number' => '9123456789',
            'code' => '123456'
        ]);

        // Then register
        $response = $this->postJson('/api/v1/auth/register', [
            'full_name' => 'John Doe',
            'mobile_number' => '9123456789',
            'username' => 'johndoe',
            'national_code' => '1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'user' => [
                    'id',
                    'full_name',
                    'mobile_number',
                    'username',
                    'referral_code'
                ],
                'gift_card' => [
                    'code',
                    'current_balance',
                    'expires_at'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'mobile_number' => '9123456789',
            'username' => 'johndoe'
        ]);

        $user = User::where('mobile_number', '9123456789')->first();
        
        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'balance' => 0
        ]);

        $this->assertDatabaseHas('gift_cards', [
            'assigned_to_user_id' => $user->id,
            'is_active' => true
        ]);
    }

    public function test_user_cannot_register_without_verifying_otp()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'full_name' => 'John Doe',
            'mobile_number' => '9123456789',
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
            'mobile_number' => '9123456789',
            'code' => '123456',
            'expires_at' => now()->addMinutes(2),
            'is_used' => false
        ]);

        $this->postJson('/api/v1/auth/verify-otp', [
            'mobile_number' => '9123456789',
            'code' => '123456'
        ]);

        // Then register with referral code
        $response = $this->postJson('/api/v1/auth/register', [
            'full_name' => 'John Doe',
            'mobile_number' => '9123456789',
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
            'mobile_number' => '9123456789',
            'referred_by_user_id' => $referrer->id
        ]);

        // Check if referral bonus was added
        $this->assertDatabaseHas('gift_cards', [
            'assigned_to_user_id' => $referrer->id,
            'current_balance' => 5000
        ]);
    }

    public function test_provider_can_register_with_valid_otp()
    {
        // Create test data in the database
        $province = \App\Models\Province::create([
            'name' => 'تهران'
        ]);

        $city = \App\Models\City::create([
            'name' => 'تهران',
            'province_id' => $province->id
        ]);

        // First verify OTP
        $otp = OtpCode::create([
            'mobile_number' => '9121234567',
            'code' => '654321',
            'expires_at' => now()->addMinutes(2),
            'is_used' => false
        ]);

        $this->postJson('/api/v1/auth/verify-otp', [
            'mobile_number' => '9121234567',
            'code' => '654321'
        ]);

        // Then register provider
        $response = $this->postJson('/api/v1/auth/register-senfi-provider', [
            'full_name' => 'Ali Provider',
            'mobile_number' => '9121234567',
            'national_code' => '1122334455',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'business_name' => 'فروشگاه علی',
            'business_license_number' => '123456789',
            'tax_id' => '987654321',
            'business_address' => 'تهران، خیابان انقلاب',
            'business_phone' => '02111112222',
            'business_hours' => [
                'saturday' => ['open' => '09:00', 'close' => '19:00'],
                'sunday' => ['open' => '09:00', 'close' => '19:00'],
                'monday' => ['open' => '09:00', 'close' => '19:00'],
                'tuesday' => ['open' => '09:00', 'close' => '19:00'],
                'wednesday' => ['open' => '09:00', 'close' => '19:00'],
                'thursday' => ['open' => '09:00', 'close' => '19:00']
            ],
            'accepted_payment_methods' => ['cash', 'pos', 'online'],
            'has_physical_store' => true,
            'base_service_fee' => 50000,
            'province_id' => $province->id,
            'city_id' => $city->id
        ]);

        if ($response->status() === 500) {
            dump($response->json());
        }

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'token',
                'user',
                'provider'
            ]);

        $this->assertDatabaseHas('users', [
            'mobile_number' => '9121234567',
            'user_type' => 'provider'
        ]);

        $user = User::where('mobile_number', '9121234567')->first();
        
        $this->assertDatabaseHas('providers', [
            'user_id' => $user->id,
            'provider_type' => 'senfi'
        ]);

        $provider = \App\Models\Provider::where('user_id', $user->id)->first();

        $this->assertDatabaseHas('provider_senfi', [
            'provider_id' => $provider->id,
            'business_name' => 'فروشگاه علی',
            'business_license_number' => '123456789',
            'tax_id' => '987654321',
            'business_phone' => '02111112222'
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'balance' => 0
        ]);

        $this->assertDatabaseHas('gift_cards', [
            'assigned_to_user_id' => $user->id,
            'is_active' => true
        ]);
    }
} 