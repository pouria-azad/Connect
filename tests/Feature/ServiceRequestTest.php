<?php

namespace Tests\Feature;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\ServiceCategory;
use App\Models\Province;
use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ServiceRequestTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_customer_can_create_private_service_request()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $serviceProvider = User::factory()->create(['role' => 'service_provider']);
        $serviceCategory = ServiceCategory::factory()->create();
        $province = Province::factory()->create();
        $city = City::factory()->create();

        $response = $this->actingAs($customer)->postJson('/api/v1/service-requests', [
            'subject' => 'Test Service Request',
            'description' => 'This is a test service request',
            'request_type' => 'private',
            'service_provider_user_id' => $serviceProvider->id,
            'service_category_id' => $serviceCategory->id,
            'province_id' => $province->id,
            'city_id' => $city->id,
            'files' => [
                UploadedFile::fake()->create('document.pdf', 100)
            ]
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'subject',
                    'description',
                    'request_type',
                    'status',
                    'customer',
                    'service_provider',
                    'service_category',
                    'province',
                    'city',
                    'files',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('service_requests', [
            'subject' => 'Test Service Request',
            'description' => 'This is a test service request',
            'request_type' => 'private',
            'status' => 'pending_payment'
        ]);
    }

    public function test_customer_can_create_public_service_request()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $serviceCategory = ServiceCategory::factory()->create();
        $province = Province::factory()->create();
        $city = City::factory()->create();

        $response = $this->actingAs($customer)->postJson('/api/v1/service-requests', [
            'subject' => 'Test Public Service Request',
            'description' => 'This is a test public service request',
            'request_type' => 'public',
            'service_category_id' => $serviceCategory->id,
            'province_id' => $province->id,
            'city_id' => $city->id,
            'scope_type' => 'city_wide'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'subject',
                    'description',
                    'request_type',
                    'status',
                    'customer',
                    'service_category',
                    'province',
                    'city',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('service_requests', [
            'subject' => 'Test Public Service Request',
            'description' => 'This is a test public service request',
            'request_type' => 'public',
            'status' => 'pending_payment'
        ]);
    }

    public function test_service_provider_can_accept_service_request()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $serviceProvider = User::factory()->create(['role' => 'service_provider']);
        $serviceRequest = ServiceRequest::factory()->create([
            'customer_user_id' => $customer->id,
            'status' => 'pending_sp_acceptance'
        ]);

        $response = $this->actingAs($serviceProvider)->postJson("/api/v1/service-requests/{$serviceRequest->id}/accept");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'accepted_service_provider',
                    'accepted_at'
                ]
            ]);

        $this->assertDatabaseHas('service_requests', [
            'id' => $serviceRequest->id,
            'status' => 'accepted_by_sp',
            'accepted_service_provider_user_id' => $serviceProvider->id
        ]);
    }

    public function test_service_provider_can_reject_service_request()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $serviceProvider = User::factory()->create(['role' => 'service_provider']);
        $serviceRequest = ServiceRequest::factory()->create([
            'customer_user_id' => $customer->id,
            'status' => 'pending_sp_acceptance'
        ]);

        $response = $this->actingAs($serviceProvider)->postJson("/api/v1/service-requests/{$serviceRequest->id}/reject", [
            'rejection_reason' => 'Not available at this time'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'rejection_reason'
                ]
            ]);

        $this->assertDatabaseHas('service_requests', [
            'id' => $serviceRequest->id,
            'status' => 'rejected_by_sp',
            'rejection_reason' => 'Not available at this time'
        ]);
    }

    public function test_service_provider_can_complete_service_request()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $serviceProvider = User::factory()->create(['role' => 'service_provider']);
        $serviceRequest = ServiceRequest::factory()->create([
            'customer_user_id' => $customer->id,
            'service_provider_user_id' => $serviceProvider->id,
            'status' => 'accepted_by_sp'
        ]);

        $response = $this->actingAs($serviceProvider)->postJson("/api/v1/service-requests/{$serviceRequest->id}/complete");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'completed_at'
                ]
            ]);

        $this->assertDatabaseHas('service_requests', [
            'id' => $serviceRequest->id,
            'status' => 'completed'
        ]);
    }

    public function test_customer_can_cancel_service_request()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $serviceRequest = ServiceRequest::factory()->create([
            'customer_user_id' => $customer->id,
            'status' => 'pending_payment'
        ]);

        $response = $this->actingAs($customer)->postJson("/api/v1/service-requests/{$serviceRequest->id}/cancel", [
            'rejection_reason' => 'Changed my mind'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'rejection_reason'
                ]
            ]);

        $this->assertDatabaseHas('service_requests', [
            'id' => $serviceRequest->id,
            'status' => 'canceled_by_customer',
            'rejection_reason' => 'Changed my mind'
        ]);
    }

    public function test_customer_can_view_own_service_requests()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $serviceRequests = ServiceRequest::factory()->count(3)->create([
            'customer_user_id' => $customer->id
        ]);

        $response = $this->actingAs($customer)->getJson('/api/v1/service-requests');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'subject',
                        'description',
                        'status',
                        'customer',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'meta' => [
                    'current_page',
                    'total'
                ]
            ]);
    }

    public function test_service_provider_can_view_own_service_requests()
    {
        $serviceProvider = User::factory()->create(['role' => 'service_provider']);
        $serviceRequests = ServiceRequest::factory()->count(3)->create([
            'service_provider_user_id' => $serviceProvider->id
        ]);

        $response = $this->actingAs($serviceProvider)->getJson('/api/v1/service-requests');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'subject',
                        'description',
                        'status',
                        'service_provider',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'meta' => [
                    'current_page',
                    'total'
                ]
            ]);
    }

    public function test_customer_cannot_accept_service_request()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $serviceRequest = ServiceRequest::factory()->create([
            'customer_user_id' => $customer->id,
            'status' => 'pending_sp_acceptance'
        ]);

        $response = $this->actingAs($customer)->postJson("/api/v1/service-requests/{$serviceRequest->id}/accept");

        $response->assertStatus(403);
    }

    public function test_service_provider_cannot_complete_unaccepted_request()
    {
        $serviceProvider = User::factory()->create(['role' => 'service_provider']);
        $serviceRequest = ServiceRequest::factory()->create([
            'status' => 'pending_sp_acceptance'
        ]);

        $response = $this->actingAs($serviceProvider)->postJson("/api/v1/service-requests/{$serviceRequest->id}/complete");

        $response->assertStatus(400);
    }

    public function test_customer_cannot_cancel_completed_request()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $serviceRequest = ServiceRequest::factory()->create([
            'customer_user_id' => $customer->id,
            'status' => 'completed'
        ]);

        $response = $this->actingAs($customer)->postJson("/api/v1/service-requests/{$serviceRequest->id}/cancel", [
            'rejection_reason' => 'Changed my mind'
        ]);

        $response->assertStatus(400);
    }
} 