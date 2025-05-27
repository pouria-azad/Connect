<?php

namespace Tests\Feature;

use App\Events\MessageSent;
use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $anotherUser;
    protected $order;
    protected $conversation;

    protected function setUp(): void
    {
        parent::setUp();

        // ایجاد کاربران
        $this->user = User::factory()->create();
        $this->anotherUser = User::factory()->create();

        // ایجاد سفارش
        $this->order = Order::factory()->create(['user_id' => $this->user->id]);

        // ایجاد مکالمه
        $this->conversation = Conversation::create(['order_id' => $this->order->id]);

        // افزودن کاربران به مکالمه
        $this->conversation->users()->attach([$this->user->id, $this->anotherUser->id]);
    }

    /** @test */
    public function user_can_view_conversation_messages()
    {
        // ایجاد چند پیام برای مکالمه
        $messages = ChatMessage::factory()->count(3)->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->user->id
        ]);

        // ارسال درخواست برای مشاهده پیام‌ها
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/conversations/{$this->conversation->id}/messages");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_cannot_view_conversation_messages_if_not_a_participant()
    {
        // کاربر جدید که در مکالمه نیست
        $nonParticipant = User::factory()->create();

        $response = $this->actingAs($nonParticipant)
            ->getJson("/api/v1/conversations/{$this->conversation->id}/messages");

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_send_message_to_conversation()
    {
        Event::fake([MessageSent::class]);

        $messageData = [
            'conversation_id' => $this->conversation->id,
            'content' => $this->faker->sentence,
            'type' => 'text'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/chat/send', $messageData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'content' => $messageData['content'],
                'sender_id' => $this->user->id
            ]);

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->user->id,
            'content' => $messageData['content']
        ]);

        Event::assertDispatched(MessageSent::class);
    }

    /** @test */
    public function user_can_mark_messages_as_read()
    {
        // ایجاد پیام خوانده نشده
        $message = ChatMessage::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->anotherUser->id,
            'is_read' => false
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/conversations/{$this->conversation->id}/read");

        $response->assertStatus(204);

        $this->assertDatabaseHas('chat_messages', [
            'id' => $message->id,
            'is_read' => true
        ]);
    }

    /** @test */
    public function user_can_block_another_user_in_conversation()
    {
        $blockData = [
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->anotherUser->id
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/conversations/{$this->conversation->id}/block", $blockData);

        $response->assertStatus(204);

        $this->assertDatabaseHas('conversation_user', [
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->anotherUser->id,
            'is_blocked' => true
        ]);
    }

    /** @test */
    public function user_can_unblock_another_user_in_conversation()
    {
        // ابتدا کاربر را بلاک می‌کنیم
        $this->conversation->users()->updateExistingPivot(
            $this->anotherUser->id, ['is_blocked' => true]
        );

        $unblockData = [
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->anotherUser->id
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/conversations/{$this->conversation->id}/unblock", $unblockData);

        $response->assertStatus(204);

        $this->assertDatabaseHas('conversation_user', [
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->anotherUser->id,
            'is_blocked' => false
        ]);
    }
}
