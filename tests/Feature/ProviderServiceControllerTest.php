<?php
// tests/Feature/ProviderServiceControllerTest.php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\Service;
use App\Models\User;
use App\Models\ProviderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderServiceControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function provider_can_view_own_services()
    {
        $user     = User::factory()->create();
        $provider = Provider::factory()->create(['user_id' => $user->id]);
        $service  = Service::factory()->create();
        $provider->services()->attach($service->id, ['price' => 100]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/providers/{$provider->id}/services");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'title', 'pivot' => ['price','custom_description']]]]);
    }

    /** @test */
    public function user_cannot_view_other_provider_services()
    {
        $user          = User::factory()->create();
        $otherProvider = Provider::factory()->create();
        Service::factory()->create()->each(function($s) use($otherProvider){
            $otherProvider->services()->attach($s->id);
        });

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/providers/{$otherProvider->id}/services");

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_view_any_provider_services()
    {
        $provider = Provider::factory()->create();
        $service  = Service::factory()->create();
        $provider->services()->attach($service->id);
        $response = $this->actingAsAdmin()->getJson("/api/v1/providers/{$provider->id}/services");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'title', 'pivot' => ['price','custom_description']]]]);
    }

    /** @test */
    public function provider_not_found_returns_404()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/providers/999/services");

        $response->assertStatus(404);
    }

    /** @test */
    public function test_provider_can_store_own_service()
    {
        $user = User::factory()->create();
        $provider = Provider::factory()->create(['user_id' => $user->id]);
        $service = Service::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/providers/{$provider->id}/services", [
                'service_id' => $service->id,
                'price' => 100,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('provider_services', [
            'provider_id' => $provider->id,
            'service_id' => $service->id,
            'price' => 100,
        ]);
    }

    /** @test */
    public function test_provider_cannot_store_service_for_other_provider()
    {
        $user = User::factory()->create();
        $otherProvider = Provider::factory()->create();
        $service = Service::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/providers/{$otherProvider->id}/services", [
                'service_id' => $service->id,
                'price' => 150,
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function test_admin_can_store_service_for_any_provider()
    {
        $provider = Provider::factory()->create();
        $service = Service::factory()->create();
        $response = $this->actingAsAdmin()->postJson("/api/v1/providers/{$provider->id}/services", [
            'service_id' => $service->id,
            'price' => 200,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('provider_services', [
            'provider_id' => $provider->id,
            'service_id' => $service->id,
            'price' => 200,
        ]);
    }

    /** @test */
    public function test_store_with_invalid_data_returns_422()
    {
        $user = User::factory()->create();
        $provider = Provider::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/providers/{$provider->id}/services", [
                'service_id' => 999,  // Invalid
                'price' => -10,  // Negative
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_provider_can_destroy_own_service()
    {
        $user = User::factory()->create();
        $provider = Provider::factory()->create(['user_id' => $user->id]);
        $service = Service::factory()->create();
        $providerService = ProviderService::factory()->create([
            'provider_id' => $provider->id,
            'service_id' => $service->id
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/providers/{$provider->id}/services/{$service->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('provider_services', [
            'id' => $providerService->id
        ]);
    }

    /** @test */
    public function test_provider_cannot_destroy_other_provider_service()
    {
        $user = User::factory()->create();
        $otherProvider = Provider::factory()->create();
        $service = Service::factory()->create();
        $providerService = ProviderService::factory()->create([
            'provider_id' => $otherProvider->id,
            'service_id' => $service->id
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/providers/{$otherProvider->id}/services/{$service->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('provider_services', [
            'id' => $providerService->id
        ]);
    }

    /** @test */
    public function test_admin_can_destroy_any_provider_service()
    {
        $provider = Provider::factory()->create();
        $service = Service::factory()->create();
        $providerService = ProviderService::factory()->create([
            'provider_id' => $provider->id,
            'service_id' => $service->id
        ]);
        $response = $this->actingAsAdmin()->deleteJson("/api/v1/admin/provider-services/" . $providerService->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('provider_services', [
            'id' => $providerService->id
        ]);
    }

    /** @test */
    public function destroy_with_nonexistent_service_returns_404()
    {
        $user     = User::factory()->create();
        $provider = Provider::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/providers/{$provider->id}/services/999");

        $response->assertStatus(404);
    }
}
