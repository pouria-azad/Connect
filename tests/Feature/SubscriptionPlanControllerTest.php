<?php

namespace Tests\Feature;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionPlanControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $userToken;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->user->wallet()->create(['balance' => 0]);
        $this->userToken = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_admin_can_list_all_plans()
    {
        // Create multiple plans
        SubscriptionPlan::factory()->count(3)->create();

        $response = $this->actingAsAdmin()->getJson('/api/v1/admin/subscription-plans');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'price',
                        'duration_days',
                        'features',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_regular_user_cannot_list_all_plans()
    {
        $response = $this->actingAsUser()->getJson('/api/v1/admin/subscription-plans');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_plan()
    {
        $planData = [
            'name' => 'Premium Plan',
            'price' => 100000,
            'duration_days' => 30,
            'features' => ['feature1', 'feature2'],
            'max_ads_count' => 10,
            'max_services_count' => 5,
            'priority_level' => 1
        ];

        $response = $this->actingAsAdmin()->postJson('/api/v1/admin/subscription-plans', $planData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'plan' => [
                    'id',
                    'name',
                    'price',
                    'duration_days',
                    'features'
                ]
            ]);

        $this->assertDatabaseHas('subscription_plans', array_merge(
            $planData,
            [
                'features' => json_encode($planData['features'])
            ]
        ));
    }

    public function test_admin_cannot_create_plan_with_invalid_data()
    {
        $response = $this->actingAsAdmin()->postJson('/api/v1/admin/subscription-plans', [
            'name' => 'P', // Too short
            'price' => -100, // Invalid price
            'duration_days' => 0, // Invalid duration
            'max_ads_count' => 1,
            'max_services_count' => 1,
            'priority_level' => 1
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price', 'duration_days']);
    }

    public function test_admin_can_update_plan()
    {
        $plan = SubscriptionPlan::factory()->create();

        $updateData = [
            'name' => 'Updated Plan',
            'price' => 150000,
            'duration_days' => 60,
            'features' => ['feature1', 'feature2', 'feature3'],
            'max_ads_count' => 20,
            'max_services_count' => 10,
            'priority_level' => 2
        ];

        $response = $this->actingAsAdmin()->putJson('/api/v1/admin/subscription-plans/' . $plan->id, $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'plan' => [
                    'id',
                    'name',
                    'price',
                    'duration_days',
                    'features'
                ]
            ]);

        $this->assertDatabaseHas('subscription_plans', array_merge(
            ['id' => $plan->id],
            $updateData,
            [
                'features' => json_encode($updateData['features'])
            ]
        ));
    }

    public function test_admin_cannot_update_nonexistent_plan()
    {
        $response = $this->actingAsAdmin()->putJson('/api/v1/admin/subscription-plans/999', [
            'name' => 'Updated Plan',
            'price' => 150000,
            'duration_days' => 60
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'طرح اشتراک یافت نشد'
            ]);
    }

    public function test_admin_can_delete_plan()
    {
        $plan = SubscriptionPlan::factory()->create();

        $response = $this->actingAsAdmin()->deleteJson('/api/v1/admin/subscription-plans/' . $plan->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'طرح اشتراک با موفقیت حذف شد'
            ]);

        $this->assertDatabaseMissing('subscription_plans', [
            'id' => $plan->id
        ]);
    }

    public function test_admin_cannot_delete_plan_with_active_subscriptions()
    {
        $plan = SubscriptionPlan::factory()->create();
        
        // Create an active subscription for this plan
        $plan->subscriptions()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'amount' => $plan->price
        ]);

        $response = $this->actingAsAdmin()->deleteJson('/api/v1/admin/subscription-plans/' . $plan->id);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'این طرح دارای اشتراک فعال است و قابل حذف نیست'
            ]);

        $this->assertDatabaseHas('subscription_plans', [
            'id' => $plan->id
        ]);
    }

    public function test_admin_can_view_plan_details()
    {
        $plan = SubscriptionPlan::factory()->create();

        $response = $this->actingAsAdmin()->getJson('/api/v1/admin/subscription-plans/' . $plan->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'plan' => [
                    'id',
                    'name',
                    'price',
                    'duration_days',
                    'features',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    public function test_admin_can_view_plan_statistics()
    {
        $plan = SubscriptionPlan::factory()->create();
        
        // Create some subscriptions for this plan
        $plan->subscriptions()->createMany([
            [
                'user_id' => $this->user->id,
                'status' => 'active',
                'start_date' => now(),
                'end_date' => now()->addDays(30),
                'amount' => $plan->price
            ],
            [
                'user_id' => User::factory()->create()->id,
                'status' => 'cancelled',
                'start_date' => now()->subDays(30),
                'end_date' => now(),
                'amount' => $plan->price
            ]
        ]);

        $response = $this->actingAsAdmin()->getJson('/api/v1/admin/subscription-plans/' . $plan->id . '/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_subscriptions',
                'active_subscriptions',
                'cancelled_subscriptions',
                'total_revenue'
            ])
            ->assertJson([
                'total_subscriptions' => 2,
                'active_subscriptions' => 1,
                'cancelled_subscriptions' => 1
            ]);
    }
} 