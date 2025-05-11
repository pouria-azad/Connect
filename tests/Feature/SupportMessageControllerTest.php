<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportMessageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_reply_to_their_ticket()
    {
        $user = User::factory()->create();
        $ticket = SupportTicket::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson('/api/v1/support/tickets/' . $ticket->id . '/reply', [
            'message' => 'لطفاً مشکل را بررسی کنید',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'پیام ارسال شد']);

        $this->assertDatabaseHas('support_messages', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => 'لطفاً مشکل را بررسی کنید',
            'is_admin' => false,
        ]);

        $this->assertEquals('open', $ticket->fresh()->status);
    }

    public function test_user_cannot_reply_to_other_user_ticket()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $ticket = SupportTicket::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2)->postJson('/api/v1/support/tickets/' . $ticket->id . '/reply', [
            'message' => 'این پیام نباید ارسال شود',
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_admin_can_reply_to_ticket()
    {
        $admin = Admin::factory()->create();
        $ticket = SupportTicket::factory()->create();

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/support/tickets/' . $ticket->id . '/reply', [
            'message' => 'مشکل بررسی شد',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'پاسخ ثبت شد']);

        $this->assertDatabaseHas('support_messages', [
            'ticket_id' => $ticket->id,
            'user_id' => null,
            'message' => 'مشکل بررسی شد',
            'is_admin' => true,
        ]);

        $this->assertEquals('answered', $ticket->fresh()->status);
    }

    public function test_non_admin_cannot_reply_as_admin()
    {
        $user = User::factory()->create();
        $ticket = SupportTicket::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/admin/support/tickets/' . $ticket->id . '/reply', [
            'message' => 'این پیام نباید ارسال شود',
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }
}
