<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_own_invoices()
    {
        $user = User::factory()->create();
        Invoice::factory()->count(3)->for($user, 'recipient_user')->create();
        $this->actingAs($user);
        $response = $this->getJson('/api/v1/invoices');
        $response->assertStatus(200)
            ->assertJsonFragment(['current_page' => 1]);
    }

    public function test_user_can_view_own_invoice_details()
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->for($user, 'recipient_user')->create();
        InvoiceItem::factory()->count(2)->for($invoice)->create();
        $this->actingAs($user);
        $response = $this->getJson("/api/v1/invoices/{$invoice->id}");
        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $invoice->id]);
    }

    public function test_user_can_create_invoice()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $data = [
            'items' => [
                ['title' => 'خدمت ۱', 'quantity' => 2, 'unit_price' => 50000],
                ['title' => 'خدمت ۲', 'quantity' => 1, 'unit_price' => 100000],
            ],
            'description' => 'توضیحات تست',
            'due_date' => now()->addDays(5)->toDateString(),
        ];
        $response = $this->postJson('/api/v1/invoices', $data);
        $response->assertStatus(201);
        $this->assertDatabaseHas('invoices', [
            'recipient_user_id' => $user->id,
            'amount' => 200000,
        ]);
    }

    public function test_user_can_pay_own_invoice()
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->for($user, 'recipient_user')->create(['status' => 'pending']);
        $this->actingAs($user);
        $response = $this->postJson("/api/v1/invoices/{$invoice->id}/pay");
        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'paid']);
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'paid',
        ]);
    }

    public function test_user_cannot_view_others_invoice()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $invoice = Invoice::factory()->for($other, 'recipient_user')->create();
        $this->actingAs($user);
        $response = $this->getJson("/api/v1/invoices/{$invoice->id}");
        $response->assertStatus(404);
    }
} 