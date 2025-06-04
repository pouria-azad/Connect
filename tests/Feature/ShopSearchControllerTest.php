<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopSearchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_products_and_services()
    {
        $product = Product::factory()->create(['name' => 'چکش تستی', 'description' => 'ابزار تستی']);
        $service = Service::factory()->create(['title' => 'سرویس تستی', 'description' => 'سرویس ابزار']);

        $response = $this->getJson('/api/v1/shop/search?q=تستی');

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'چکش تستی']);
        $response->assertJsonFragment(['title' => 'سرویس تستی']);
    }

    public function test_search_requires_query_param()
    {
        $response = $this->getJson('/api/v1/shop/search');
        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'عبارت جستجو الزامی است']);
    }
} 