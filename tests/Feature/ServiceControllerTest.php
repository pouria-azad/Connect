<?php
// tests/Feature/ServiceControllerTest.php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_returns_all_services()
    {
        $parentService = Service::factory()->create();
        Service::factory()->create(['parent_id' => $parentService->id]);

        $response = $this->getJson('/api/v1/services');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'title', 'children']]]);
    }

    /** @test */
    public function show_returns_service_details()
    {
        $service = Service::factory()->create();

        $response = $this->getJson("/api/v1/services/{$service->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id'   => $service->id,
                'title' => $service->title,
            ]);
    }

    /** @test */
    public function show_with_nonexistent_service_returns_404()
    {
        $response = $this->getJson('/api/v1/services/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function admin_can_store_service()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/services', [
            'title'        => 'New Service',
            'description' => 'Service Description',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('services', ['title' => 'New Service']);
    }

    /** @test */
    public function user_cannot_store_service()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/services', [
            'name' => 'New Service',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function store_with_invalid_data_returns_422()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/services', [
            'name' => '', // Invalid: empty
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function admin_can_update_service()
    {
        $admin   = Admin::factory()->create();
        $service = Service::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')->putJson("/api/v1/services/{$service->id}", [
            'title'        => 'Updated Service',
            'description' => 'Updated Description',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('services', [
            'id'   => $service->id,
            'title' => 'Updated Service',
        ]);
    }

    /** @test */
    public function user_cannot_update_service()
    {
        $user    = User::factory()->create();
        $service = Service::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->putJson("/api/v1/services/{$service->id}", [
            'name' => 'Attempted Update',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function update_with_invalid_data_returns_422()
    {
        $admin   = Admin::factory()->create();
        $service = Service::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')->putJson("/api/v1/services/{$service->id}", [
            'title' => '', // Invalid
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function admin_can_destroy_service()
    {
        $admin   = Admin::factory()->create();
        $service = Service::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/v1/services/{$service->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    /** @test */
    public function user_cannot_destroy_service()
    {
        $user    = User::factory()->create();
        $service = Service::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/services/{$service->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function destroy_with_nonexistent_service_returns_404()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')->deleteJson('/api/v1/services/999');

        $response->assertStatus(404);
    }
}
