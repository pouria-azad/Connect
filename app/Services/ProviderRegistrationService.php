<?php

namespace App\Services;

use App\Models\User;
use App\Models\Provider;
use App\Models\ProviderSenfi;
use App\Models\ProviderCanctyar;
use App\Models\Wallet;
use App\Models\ClubWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProviderRegistrationService
{
    public function register(array $data, string $providerType): array
    {
        try {
            DB::beginTransaction();

            // Create user
            $user = $this->createUser($data, $providerType);

            // Create provider base profile
            $provider = $this->createProvider($user, $data, $providerType);

            // Create specific provider type profile
            $specificProvider = $this->createSpecificProvider($provider, $data, $providerType);

            // Create wallets
            $this->createWallets($user);

            // Create gift card for provider
            $this->createGiftCard($user);

            DB::commit();

            // Generate token
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'message' => 'ثبت‌نام با موفقیت انجام شد',
                'token' => $token,
                'user' => $user->load('wallet', 'clubWallet'),
                'provider' => $provider->load($providerType)
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function createUser(array $data, string $providerType): User
    {
        return User::create([
            'mobile_number' => $data['mobile_number'],
            'password' => Hash::make($data['password']),
            'full_name' => $data['full_name'],
            'national_code' => $data['national_code'],
            'user_type' => 'provider',
            'referral_code' => strtoupper(Str::random(8)),
            'province_id' => $data['province_id'],
            'city_id' => $data['city_id'],
            'can_serve_nation_wide' => $providerType === 'canctyar' 
                ? (isset($data['service_areas']) && count($data['service_areas']) > 1)
                : false
        ]);
    }

    private function createProvider(User $user, array $data, string $providerType): Provider
    {
        return Provider::create([
            'user_id' => $user->id,
            'provider_type' => $providerType,
            'bio' => $data['bio'] ?? null,
            'shop_name' => $data['shop_name'] ?? null,
            'senfi_number' => $data['senfi_number'] ?? null,
            'occupation_id' => $data['occupation_id'] ?? null,
            'province_id' => $data['province_id'],
            'city_id' => $data['city_id'],
            'can_serve_nation_wide' => $user->can_serve_nation_wide
        ]);
    }

    private function createSpecificProvider(Provider $provider, array $data, string $providerType)
    {
        if ($providerType === 'senfi') {
            return ProviderSenfi::create([
                'provider_id' => $provider->id,
                'business_name' => $data['business_name'],
                'business_license_number' => $data['business_license_number'],
                'tax_id' => $data['tax_id'],
                'business_address' => $data['business_address'],
                'business_phone' => $data['business_phone'],
                'business_hours' => $data['business_hours'],
                'accepted_payment_methods' => $data['accepted_payment_methods'],
                'has_physical_store' => $data['has_physical_store'],
                'portfolio_images' => $data['portfolio_images'] ?? [],
                'tags' => $data['tags'] ?? [],
                'base_service_fee' => $data['base_service_fee']
            ]);
        } else {
            return ProviderCanctyar::create([
                'provider_id' => $provider->id,
                'certification_number' => $data['certification_number'],
                'skills' => $data['skills'],
                'service_areas' => $data['service_areas'],
                'availability_hours' => $data['availability_hours'],
                'can_travel' => $data['can_travel'],
                'travel_fee_per_km' => $data['travel_fee_per_km'],
                'minimum_service_fee' => $data['minimum_service_fee'],
                'portfolio_images' => $data['portfolio_images'] ?? [],
                'tags' => $data['tags'] ?? []
            ]);
        }
    }

    private function createWallets(User $user): void
    {
        $user->wallet()->create(['balance' => 0]);
        $user->clubWallet()->create(['points' => 0]);
    }

    private function createGiftCard(User $user): void
    {
        $user->assignedGiftCards()->create([
            'code' => strtoupper(Str::random(10)),
            'initial_balance' => 0,
            'amount' => 0,
            'current_balance' => 0,
            'expires_at' => now()->addMonths(6),
            'is_active' => true,
            'source_type' => 'admin_issued',
        ]);
    }
} 