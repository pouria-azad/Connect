<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserBankCard;
use App\Models\Wallet;
use App\Models\WalletWithdrawRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletWithdrawTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->wallet()->create(['balance' => 0]);
    }

    public function test_user_can_request_withdrawal()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100000
        ]);
        $bankCard = UserBankCard::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/wallet/withdraw', [
                'amount' => 50000,
                'bank_card_id' => $bankCard->id
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'درخواست برداشت با موفقیت ثبت شد'
            ]);

        $this->assertDatabaseHas('wallet_withdraw_requests', [
            'user_id' => $user->id,
            'bank_card_id' => $bankCard->id,
            'amount' => 50000,
            'status' => 'pending'
        ]);
    }

    public function test_cannot_withdraw_more_than_balance()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 10000
        ]);
        $bankCard = UserBankCard::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/wallet/withdraw', [
                'amount' => 50000,
                'bank_card_id' => $bankCard->id
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'موجودی کافی نیست'
            ]);
    }

    public function test_admin_can_approve_withdrawal_request()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100000
        ]);
        $bankCard = UserBankCard::factory()->create([
            'user_id' => $user->id
        ]);
        $withdrawRequest = WalletWithdrawRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 50000,
            'bank_card_id' => $bankCard->id
        ]);
        $response = $this->actingAsAdmin()->postJson("/api/v1/admin/wallet/withdraw-requests/{$withdrawRequest->id}/review", [
            'status' => 'approved',
            'admin_note' => 'درخواست تایید شد'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'درخواست با موفقیت بررسی شد'
            ]);

        $this->assertDatabaseHas('wallet_withdraw_requests', [
            'id' => $withdrawRequest->id,
            'status' => 'approved',
            'reviewed_by_user_id' => \App\Models\User::where('is_admin', true)->latest()->first()->id
        ]);

        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'balance' => 50000
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'amount' => -50000,
            'type' => 'withdraw'
        ]);
    }

    public function test_admin_can_reject_withdrawal_request()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100000
        ]);
        $bankCard = UserBankCard::factory()->create([
            'user_id' => $user->id
        ]);
        $withdrawRequest = WalletWithdrawRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 50000,
            'bank_card_id' => $bankCard->id
        ]);
        $response = $this->actingAsAdmin()->postJson("/api/v1/admin/wallet/withdraw-requests/{$withdrawRequest->id}/review", [
            'status' => 'rejected',
            'admin_note' => 'درخواست به دلیل نقص مدارک رد شد'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'درخواست با موفقیت بررسی شد'
            ]);

        $this->assertDatabaseHas('wallet_withdraw_requests', [
            'id' => $withdrawRequest->id,
            'status' => 'rejected',
            'reviewed_by_user_id' => \App\Models\User::where('is_admin', true)->latest()->first()->id
        ]);

        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'balance' => 100000
        ]);
    }

    public function test_non_admin_cannot_review_withdrawal_request()
    {
        $user = User::factory()->create();
        $withdrawRequest = WalletWithdrawRequest::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/v1/admin/wallet/withdraw-requests/{$withdrawRequest->id}/review", [
                'status' => 'approved',
                'admin_note' => 'درخواست تایید شد'
            ]);

        $response->assertStatus(403);
    }
} 