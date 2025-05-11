<?php

use App\Models\Admin;
use App\Models\WalletWithdrawRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\WalletTransaction;
use Laravel\Sanctum\Sanctum;

class WalletControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_deposit()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/wallet/deposit', [
            'amount' => 20000,
            'description' => 'شارژ برای خرید'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'شارژ انجام شد',
            'balance' => 20000
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id,
            'amount' => 20000,
            'type' => 'deposit'
        ]);
        $user->refresh();
        $this->assertEquals(20000, $user->balance);
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
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $transaction = WalletTransaction::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/v1/wallet/transactions');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'amount' => $transaction->amount,
            'type' => $transaction->type,
        ]);
    }

    public function test_user_can_request_withdraw()
    {
        $user = User::factory()->create(['balance' => 50000]);

        $response = $this->actingAs($user)->postJson('/api/v1/wallet/withdraw', [
            'amount' => 10000
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'درخواست برداشت ثبت شد']);

        $this->assertDatabaseHas('wallet_withdraw_requests', [
            'user_id' => $user->id,
            'amount' => 10000,
            'status' => 'pending'
        ]);

        $admin = Admin::factory()->create();

        $withdrawRequestId = $response->json('id');

        $reviewResponse = $this->actingAs($admin)->postJson("/api/v1/wallet/withdraw-review/{$withdrawRequestId}", [
            'status' => 'approved',
            'admin_note' => 'واریز انجام شد'
        ]);

        $reviewResponse->assertStatus(200);
        $reviewResponse->assertJson(['message' => 'درخواست با موفقیت بررسی شد']);

        $this->assertDatabaseHas('wallet_withdraw_requests', [
            'id' => $withdrawRequestId,
            'status' => 'approved',
            'admin_note' => 'واریز انجام شد'
        ]);

        $user->refresh();
        $this->assertEquals(40000, $user->balance);
    }

    public function test_user_with_insufficient_balance_cannot_request_withdraw()
    {
        $user = User::factory()->create(['balance' => 5000]);


        $response = $this->actingAs($user)->postJson('/api/v1/wallet/withdraw', [
            'amount' => 10000
        ]);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'موجودی کافی نیست']);
    }

    public function test_admin_can_review_withdraw_request()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create(['balance' => 50000]);


        $withdrawRequest = WalletWithdrawRequest::create([
            'user_id' => $user->id,
            'amount' => 10000,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($admin)->postJson("/api/v1/wallet/withdraw-review/{$withdrawRequest->id}", [
            'status' => 'approved',
            'admin_note' => 'واریز انجام شد'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'درخواست با موفقیت بررسی شد']);

        $withdrawRequest->refresh();
        $this->assertEquals('approved', $withdrawRequest->status);
    }

    public function test_non_admin_user_cannot_review_withdraw_request()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $withdrawRequest = WalletWithdrawRequest::create([
            'user_id' => $user->id,
            'amount' => 10000,
            'status' => 'pending'
        ]);

        $response = $this->postJson("/api/v1/wallet/withdraw-review/{$withdrawRequest->id}", [
            'status' => 'approved',
            'admin_note' => 'واریز انجام شد'
        ]);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'This action is unauthorized.']);
    }
}

