<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SupportTicketControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_their_support_tickets()
    {
        $user = User::factory()->create();
        SupportTicket::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/v1/support/tickets');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_user_can_create_support_ticket()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/support/tickets', [
            'subject' => 'مشکل پرداخت',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'تیکت ایجاد شد',
                'data' => [
                    'subject' => 'مشکل پرداخت',
                    'status' => 'open',
                ]
            ]);

        $this->assertDatabaseHas('support_tickets', [
            'subject' => 'مشکل پرداخت',
            'user_id' => $user->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_tickets()
    {
        $response = $this->getJson('/api/v1/support/tickets');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_admin_can_get_all_support_tickets()
    {
        $admin = Admin::factory()->create();
        SupportTicket::factory()->count(5)->create();

        $response = $response = $this->actingAs($admin)->getJson('/api/v1/admin/support/tickets');

        $response->assertStatus(200)
            ->assertJsonCount(5);
    }

    public function test_non_admin_cannot_get_all_support_tickets()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->getJson('/api/v1/admin/support/tickets');

        $response->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_user_can_get_specific_ticket_details()
    {
        $user = User::factory()->create();
        $ticket = SupportTicket::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/v1/support/tickets/' . $ticket->id);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $ticket->id,
                'subject' => $ticket->subject,
            ]);
    }

    public function test_user_cannot_get_other_user_ticket_details()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $ticket = SupportTicket::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2, 'sanctum')->getJson('/api/v1/support/tickets/' . $ticket->id);

        $response->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }
}
