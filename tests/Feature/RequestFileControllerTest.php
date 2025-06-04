<?php

namespace Tests\Feature;

use App\Models\RequestFile;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RequestFileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_files_of_service_request()
    {
        $user = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create([
            'customer_user_id' => $user->id,
        ]);
        $file = RequestFile::factory()->for($serviceRequest, 'serviceRequest')->create();
        $this->actingAs($user);
        $response = $this->getJson("/api/v1/service-requests/{$serviceRequest->id}/files");
        $response->assertOk()->assertJsonFragment(['id' => $file->id]);
    }

    public function test_user_can_delete_own_file()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create([
            'customer_user_id' => $user->id,
        ]);
        $file = new \App\Models\RequestFile([
            'request_id' => $serviceRequest->id,
            'file_url' => '/storage/testfile.jpg',
            'file_name' => 'testfile.jpg',
            'file_size' => 1234,
            'file_type' => 'image/jpeg',
        ]);
        $file->save();
        Storage::disk('public')->put('testfile.jpg', 'dummy');
        $this->actingAs($user);
        $response = $this->deleteJson("/api/v1/request-files/{$file->id}");
        $response->assertOk();
        $this->assertDatabaseMissing('request_files', ['id' => $file->id]);
        Storage::disk('public')->assertMissing('testfile.jpg');
    }

    public function test_user_can_download_own_file()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create([
            'customer_user_id' => $user->id,
        ]);
        $file = new \App\Models\RequestFile([
            'request_id' => $serviceRequest->id,
            'file_url' => '/storage/testfile2.jpg',
            'file_name' => 'myfile.jpg',
            'file_size' => 4321,
            'file_type' => 'image/jpeg',
        ]);
        $file->save();
        Storage::disk('public')->put('testfile2.jpg', 'dummy content');
        $this->actingAs($user);
        $response = $this->get("/api/v1/request-files/{$file->id}/download");
        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=myfile.jpg');
    }
} 