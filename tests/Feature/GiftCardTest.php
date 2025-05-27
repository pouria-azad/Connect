<?php

namespace Tests\Feature;

use App\Models\GiftCard;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_use_valid_gift_card()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 0]);
        
        $giftCard = GiftCard::factory()->create([
            'initial_balance' => 50000,
            'current_balance' => 50000,
            'expires_at' => now()->addDays(30),
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/wallet/use-gift-card', [
                'code' => $giftCard->code
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'کارت هدیه با موفقیت استفاده شد',
                'balance' => 50000
            ]);

        $this->assertDatabaseHas('gift_cards', [
            'id' => $giftCard->id,
            'is_used' => true,
            'used_by_user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'amount' => 50000,
            'type' => 'deposit_gift_card',
        ]);
    }

    public function test_cannot_use_expired_gift_card()
    {
        $user = User::factory()->create();
        Wallet::factory()->create(['user_id' => $user->id]);
        
        $giftCard = GiftCard::factory()
            ->expired()
            ->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/wallet/use-gift-card', [
                'code' => $giftCard->code
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'کارت هدیه نامعتبر است'
            ]);
    }

    public function test_cannot_use_already_used_gift_card()
    {
        $user = User::factory()->create();
        Wallet::factory()->create(['user_id' => $user->id]);
        
        $giftCard = GiftCard::factory()
            ->used()
            ->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/wallet/use-gift-card', [
                'code' => $giftCard->code
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'کارت هدیه نامعتبر است'
            ]);
    }

    public function test_cannot_use_inactive_gift_card()
    {
        $user = User::factory()->create();
        Wallet::factory()->create(['user_id' => $user->id]);
        
        $giftCard = GiftCard::factory()
            ->inactive()
            ->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/wallet/use-gift-card', [
                'code' => $giftCard->code
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'کارت هدیه نامعتبر است'
            ]);
    }
} 