<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserBlock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserBlockControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_block_and_unblock_another_user()
    {
        $blocker = User::factory()->create();
        $blocked = User::factory()->create();

        $this->actingAs($blocker);

        // Block user
        $response = $this->postJson("/api/v1/users/{$blocked->id}/block", [
            'reason' => 'test reason',
        ]);
        $response->assertStatus(200)
            ->assertJson(['message' => 'User blocked successfully.']);
        $this->assertDatabaseHas('user_blocks', [
            'blocker_user_id' => $blocker->id,
            'blocked_user_id' => $blocked->id,
            'is_active' => true,
        ]);

        // Unblock user
        $response = $this->postJson("/api/v1/users/{$blocked->id}/unblock");
        $response->assertStatus(200)
            ->assertJson(['message' => 'User unblocked successfully.']);
        $this->assertDatabaseHas('user_blocks', [
            'blocker_user_id' => $blocker->id,
            'blocked_user_id' => $blocked->id,
            'is_active' => false,
        ]);
    }

    public function test_user_cannot_block_self()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->postJson("/api/v1/users/{$user->id}/block");
        $response->assertStatus(422)
            ->assertJson(['message' => 'You cannot block yourself.']);
    }

    public function test_unblock_nonexistent_block_returns_404()
    {
        $blocker = User::factory()->create();
        $blocked = User::factory()->create();
        $this->actingAs($blocker);
        $response = $this->postJson("/api/v1/users/{$blocked->id}/unblock");
        $response->assertStatus(404)
            ->assertJson(['message' => 'No active block found.']);
    }
} 