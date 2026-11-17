<?php

namespace Tests\Feature;

use Database\Seeders\InitialContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StagingPasswordLockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(InitialContentSeeder::class);
    }

    private function lock(): void
    {
        config(['app.staging_auth.user' => 'team', 'app.staging_auth.password' => 'S3cret-test-pass']);
    }

    public function test_site_is_open_when_no_lock_is_configured(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_locked_test_copy_asks_for_a_password(): void
    {
        $this->lock();

        $this->get('/')->assertUnauthorized()->assertHeader('WWW-Authenticate');
        $this->get('/admin/login')->assertUnauthorized();
        $this->withHeader('Authorization', 'Basic '.base64_encode('team:wrong'))->get('/')->assertUnauthorized();
    }

    public function test_correct_password_opens_the_test_copy(): void
    {
        $this->lock();

        $this->withHeader('Authorization', 'Basic '.base64_encode('team:S3cret-test-pass'))->get('/')->assertOk();
    }

    public function test_health_check_stays_open_for_the_deploy_pipeline(): void
    {
        $this->lock();

        $this->get('/up')->assertOk();
    }
}
