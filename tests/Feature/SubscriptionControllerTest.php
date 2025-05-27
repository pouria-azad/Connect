<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $plan;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->user->wallet()->create(['balance' => 0]);
        $this->token = $this->user->createToken('test-token')->plainTextToken;
        
        $this->plan = SubscriptionPlan::factory()->create([
            'name' => 'Premium Plan',
            'price' => 100000,
            'duration_days' => 30,
            'features' => ['feature1', 'feature2']
        ]);
    }

    public function test_user_can_list_available_plans()
    {
        // Create multiple plans
        SubscriptionPlan::factory()->count(2)->create();

        $response = $this->actingAsUser()->getJson('/api/v1/subscription-plans');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'price',
                        'duration_days',
                        'features'
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data'); // Including the plan created in setUp
    }

    public function test_user_can_list_their_subscriptions()
    {
        // Create subscriptions for the user
        Subscription::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'subscription_plan_id' => $this->plan->id,
            'amount' => $this->plan->price
        ]);

        $response = $this->actingAsUser()->getJson('/api/v1/subscriptions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'subscription_plan_id',
                        'status',
                        'start_date',
                        'end_date',
                        'amount',
                        'created_at',
                        'updated_at',
                        'plan' => [
                            'id',
                            'name',
                            'price',
                            'duration_days',
                            'features'
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_view_their_subscription_details()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'subscription_plan_id' => $this->plan->id,
            'amount' => $this->plan->price
        ]);

        $response = $this->actingAsUser()->getJson('/api/v1/subscriptions/' . $subscription->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'subscription' => [
                    'id',
                    'user_id',
                    'subscription_plan_id',
                    'status',
                    'start_date',
                    'end_date',
                    'amount',
                    'created_at',
                    'updated_at',
                    'plan' => [
                        'id',
                        'name',
                        'price',
                        'duration_days',
                        'features'
                    ]
                ]
            ]);
    }

    public function test_user_cannot_view_another_users_subscription()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => User::factory()->create()->id,
            'subscription_plan_id' => $this->plan->id,
            'amount' => $this->plan->price
        ]);

        $response = $this->actingAsUser()->getJson('/api/v1/subscriptions/' . $subscription->id);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'شما مجاز به مشاهده این اشتراک نیستید'
            ]);
    }

    public function test_user_can_cancel_their_subscription()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'subscription_plan_id' => $this->plan->id,
            'status' => 'active',
            'amount' => $this->plan->price
        ]);

        $response = $this->actingAsUser()->postJson('/api/v1/subscriptions/' . $subscription->id . '/cancel');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'اشتراک با موفقیت لغو شد'
            ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => 'cancelled'
        ]);
    }

    public function test_user_cannot_cancel_already_cancelled_subscription()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'subscription_plan_id' => $this->plan->id,
            'status' => 'cancelled',
            'amount' => $this->plan->price
        ]);

        $response = $this->actingAsUser()->postJson('/api/v1/subscriptions/' . $subscription->id . '/cancel');

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'این اشتراک قبلاً لغو شده است'
            ]);
    }

    public function test_user_can_subscribe_to_plan()
    {
        $response = $this->actingAsUser()->postJson('/api/v1/subscriptions/subscribe', [
            'plan_id' => $this->plan->id,
            'payment_method' => 'wallet'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'subscription' => [
                    'id',
                    'user_id',
                    'subscription_plan_id',
                    'status',
                    'start_date',
                    'end_date',
                    'amount'
                ]
            ]);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $this->user->id,
            'subscription_plan_id' => $this->plan->id,
            'status' => 'active',
            'amount' => $this->plan->price
        ]);
    }

    public function test_user_cannot_subscribe_to_nonexistent_plan()
    {
        $response = $this->actingAsUser()->postJson('/api/v1/subscriptions/subscribe', [
            'plan_id' => 999,
            'plan_id' => 999
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'طرح اشتراک یافت نشد'
            ]);
    }

    public function test_user_cannot_subscribe_to_plan_with_active_subscription()
    {
        // Create an active subscription
        Subscription::factory()->create([
            'user_id' => $this->user->id,
            'subscription_plan_id' => $this->plan->id,
            'status' => 'active',
            'amount' => $this->plan->price
        ]);

        $response = $this->actingAsUser()->postJson('/api/v1/subscriptions/subscribe', [
            'plan_id' => $this->plan->id
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'شما در حال حاضر یک اشتراک فعال دارید'
            ]);
    }
} 