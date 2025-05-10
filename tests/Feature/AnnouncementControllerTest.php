<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_active_announcements()
    {
        $user = User::factory()->create(); // ایجاد یه کاربر
        Announcement::factory()->count(2)->create(['is_active' => true]);
        Announcement::factory()->create(['is_active' => false]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/announcements');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_admin_can_create_announcement()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/announcements', [
            'title' => 'اعلان مهم',
            'message' => 'متن اعلان',
            'is_active' => true,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'اعلان ایجاد شد',
                'data' => [
                    'title' => 'اعلان مهم',
                    'message' => 'متن اعلان',
                    'is_active' => true,
                ]
            ]);

        $this->assertDatabaseHas('announcements', [
            'title' => 'اعلان مهم',
            'message' => 'متن اعلان',
            'is_active' => true,
        ]);
    }

    public function test_non_admin_cannot_create_announcement()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->postJson('/api/v1/admin/announcements', [
            'title' => 'اعلان غیرمجاز',
            'message' => 'متن اعلان',
            'is_active' => true,
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_admin_can_update_announcement()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $announcement = Announcement::factory()->create();

        $response = $this->actingAs($admin)->putJson('/api/v1/admin/announcements/' . $announcement->id, [
            'title' => 'اعلان به‌روزرسانی‌شده',
            'message' => 'متن جدید',
            'is_active' => false,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'اعلان به‌روزرسانی شد',
                'data' => [
                    'title' => 'اعلان به‌روزرسانی‌شده',
                    'message' => 'متن جدید',
                    'is_active' => false,
                ]
            ]);

        $this->assertDatabaseHas('announcements', [
            'id' => $announcement->id,
            'title' => 'اعلان به‌روزرسانی‌شده',
            'message' => 'متن جدید',
            'is_active' => false,
        ]);
    }

    public function test_non_admin_cannot_delete_announcement()
    {
        $user = User::factory()->create(['role' => 'user']);
        $announcement = Announcement::factory()->create();

        $response = $this->actingAs($user)->deleteJson('/api/v1/admin/announcements/' . $announcement->id);

        $response->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }
}
