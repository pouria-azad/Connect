<?php

namespace Tests\Feature;

use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuggestedProviderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_suggested_providers_are_sorted_by_rating_and_orders()
    {
        $p1 = Provider::factory()->create(['average_rating' => 4.8, 'successful_orders_count' => 10]);
        $p2 = Provider::factory()->create(['average_rating' => 4.9, 'successful_orders_count' => 5]);
        $p3 = Provider::factory()->create(['average_rating' => 4.8, 'successful_orders_count' => 20]);

        $response = $this->getJson('/api/v1/providers/suggested');
        $response->assertStatus(200);
        $ids = array_column($response->json('data'), 'id');
        // باید p2 اول باشد (امتیاز بالاتر)، بعد p3 (سفارش موفق بیشتر)، بعد p1
        $this->assertEquals([$p2->id, $p3->id, $p1->id], $ids);
    }

    public function test_suggested_providers_pagination()
    {
        Provider::factory()->count(30)->create(['average_rating' => 4.5, 'successful_orders_count' => 5]);
        $response = $this->getJson('/api/v1/providers/suggested?per_page=10');
        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
    }
} 