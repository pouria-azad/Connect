<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    protected $user;
    protected $admin;
    protected $userToken;
    protected $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a regular user
        $this->user = User::factory()->create();
        $this->userToken = $this->user->createToken('test-token')->plainTextToken;
    }

    protected function actingAsUser()
    {
        return $this->actingAs($this->user, 'sanctum');
    }

    protected function actingAsAdmin()
    {
        if (!$this->admin) {
            $this->admin = \App\Models\User::factory()->create(['is_admin' => true]);
        }
        return $this->actingAs($this->admin, 'sanctum');
    }

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }
}
