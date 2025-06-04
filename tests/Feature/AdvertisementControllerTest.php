<?php

namespace Tests\Feature;

use App\Models\Advertisement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvertisementControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_renew_own_advertisement()
    {
        $user = User::factory()->create();
        $ad = Advertisement::factory()->for($user)->create([
            'end_date' => now()->addDays(5),
            'status' => 'published',
        ]);
        $this->actingAs($user);
        $newEndDate = now()->addDays(30)->toDateString();
        $response = $this->postJson("/api/v1/my-advertisements/{$ad->id}/renew", [
            'new_end_date' => $newEndDate,
        ]);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'advertisement',
                'payment_transaction',
            ]);
        $ad->refresh();
        $this->assertEquals($newEndDate, $ad->end_date->toDateString());
        $this->assertEquals('pending', $ad->status);
        $this->assertDatabaseHas('advertisement_payment_transactions', [
            'advertisement_id' => $ad->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    public function test_user_cannot_renew_other_users_advertisement()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $ad = Advertisement::factory()->for($otherUser)->create([
            'end_date' => now()->addDays(5),
            'status' => 'published',
        ]);
        $this->actingAs($user);
        $response = $this->postJson("/api/v1/my-advertisements/{$ad->id}/renew", [
            'new_end_date' => now()->addDays(30)->toDateString(),
        ]);
        $response->assertStatus(404);
    }

    public function test_renew_fails_with_invalid_date()
    {
        $user = User::factory()->create();
        $ad = Advertisement::factory()->for($user)->create([
            'end_date' => now()->addDays(5),
            'status' => 'published',
        ]);
        $this->actingAs($user);
        $response = $this->postJson("/api/v1/my-advertisements/{$ad->id}/renew", [
            'new_end_date' => now()->toDateString(), // not after end_date
        ]);
        $response->assertStatus(422);
    }
} 