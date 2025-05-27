<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_reviews()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        Review::factory()->count(3)->create(['product_id' => $product->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/reviews?product_id=' . $product->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'user_id',
                            'product_id',
                            'rating',
                            'comment',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'total'
                ]
            ]);
    }

    public function test_user_can_create_review()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $provider = \App\Models\Provider::factory()->create();
        $service = \App\Models\Service::factory()->create();

        // Simulate user has purchased the product
        $user->orders()->create([
            'status' => 'completed',
            'total_amount' => 1000,
            'provider_id' => $provider->id,
            'service_id' => $service->id,
            'total_price' => 1000
        ])->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 1000
        ]);

        $serviceRequest = \App\Models\ServiceRequest::factory()->create([
            'customer_user_id' => $user->id
        ]);
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/reviews', [
                'product_id' => $product->id,
                'rating' => 5,
                'comment' => 'Great product! Very satisfied with the purchase.',
                'service_request_id' => $serviceRequest->id
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'product_id',
                    'rating',
                    'comment',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5
        ]);
    }

    public function test_user_cannot_review_product_without_purchase()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/reviews', [
                'product_id' => $product->id,
                'rating' => 5,
                'comment' => 'Great product!'
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'You can only review products you have purchased'
            ]);
    }

    public function test_user_cannot_review_same_product_twice()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        // Create first review
        Review::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/reviews', [
                'product_id' => $product->id,
                'rating' => 4,
                'comment' => 'Second review attempt'
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => 'You have already reviewed this product'
            ]);
    }

    public function test_user_can_update_own_review()
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/reviews/' . $review->id, [
                'rating' => 4,
                'comment' => 'Updated review comment'
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'product_id',
                    'rating',
                    'comment',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 4,
            'comment' => 'Updated review comment'
        ]);
    }

    public function test_user_cannot_update_other_user_review()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/reviews/' . $review->id, [
                'rating' => 4,
                'comment' => 'Updated review comment'
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'You are not authorized to update this review'
            ]);
    }

    public function test_user_can_delete_own_review()
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/reviews/' . $review->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Review deleted successfully'
            ]);

        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
    }

    public function test_user_cannot_delete_other_user_review()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/reviews/' . $review->id);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'You are not authorized to delete this review'
            ]);
    }
} 