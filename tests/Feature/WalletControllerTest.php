<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\UserBankCard;
use App\Models\WalletWithdrawRequest;

class WalletControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->wallet()->create(['balance' => 0]);
    }

    public function test_user_can_deposit()
    {
        $response = $this->actingAsUser()
            ->postJson('/api/v1/wallet/deposit', [
                'amount' => 20000,
                'description' => 'شارژ برای خرید'
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'شارژ انجام شد',
            'balance' => 20000
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $this->user->wallet->id,
            'amount' => 20000,
            'type' => 'deposit'
        ]);
        
        $this->user->wallet->refresh();
        $this->assertEquals(20000, $this->user->wallet->balance);
    }

    public function test_user_without_authentication_cannot_deposit()
    {
        $response = $this->postJson('/api/v1/wallet/deposit', [
            'amount' => 20000,
            'description' => 'شارژ برای خرید'
        ]);

        $response->assertStatus(401);
        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_user_can_view_own_wallet_transactions()
    {
        $transaction = WalletTransaction::factory()->create([
            'wallet_id' => $this->user->wallet->id
        ]);

        $response = $this->actingAsUser()
            ->getJson('/api/v1/wallet/transactions');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'amount' => $transaction->amount,
            'type' => $transaction->type,
        ]);
    }

    public function test_user_can_request_withdraw()
    {
        $this->user->wallet->update(['balance' => 20000]);
        $bankCard = UserBankCard::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAsUser()
            ->postJson('/api/v1/wallet/withdraw', [
                'amount' => 10000,
                'bank_card_id' => $bankCard->id
            ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'درخواست برداشت با موفقیت ثبت شد']);

        $this->assertDatabaseHas('wallet_withdraw_requests', [
            'user_id' => $this->user->id,
            'amount' => 10000,
            'status' => 'pending',
            'bank_card_id' => $bankCard->id
        ]);
    }

    public function test_user_with_insufficient_balance_cannot_request_withdraw()
    {
        $this->user->wallet->update(['balance' => 5000]);
        $bankCard = UserBankCard::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAsUser()
            ->postJson('/api/v1/wallet/withdraw', [
                'amount' => 10000,
                'bank_card_id' => $bankCard->id
            ]);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'موجودی کافی نیست']);
    }

    public function test_admin_can_review_withdraw_request()
    {
        $this->user->wallet->update(['balance' => 50000]);
        $bankCard = UserBankCard::factory()->create(['user_id' => $this->user->id]);
        $withdrawRequest = WalletWithdrawRequest::create([
            'user_id' => $this->user->id,
            'amount' => 10000,
            'status' => 'pending',
            'bank_card_id' => $bankCard->id
        ]);
        $response = $this->actingAsAdmin()->postJson("/api/v1/admin/wallet/withdraw-requests/{$withdrawRequest->id}/review", [
            'status' => 'approved',
            'admin_note' => 'واریز انجام شد'
        ]);
        $response->assertStatus(200);
        $response->assertJson(['message' => 'درخواست با موفقیت بررسی شد']);
        $withdrawRequest->refresh();
        $this->assertEquals('approved', $withdrawRequest->status);
        $this->assertEquals(\App\Models\User::where('is_admin', true)->latest()->first()->id, $withdrawRequest->reviewed_by_user_id);
    }

    public function test_non_admin_user_cannot_review_withdraw_request()
    {
        $this->user->wallet->update(['balance' => 50000]);
        $bankCard = UserBankCard::factory()->create(['user_id' => $this->user->id]);

        $withdrawRequest = WalletWithdrawRequest::create([
            'user_id' => $this->user->id,
            'amount' => 10000,
            'status' => 'pending',
            'bank_card_id' => $bankCard->id
        ]);

        $response = $this->actingAsUser()
            ->postJson("/api/v1/admin/wallet/withdraw-requests/{$withdrawRequest->id}/review", [
                'status' => 'approved',
                'admin_note' => 'واریز انجام شد'
            ]);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'This action is unauthorized.']);
    }
}

