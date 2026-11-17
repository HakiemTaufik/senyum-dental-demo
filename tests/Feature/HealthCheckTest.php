<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_check_passes_when_everything_works(): void
    {
        $this->get('/up')->assertOk();
    }

    public function test_health_check_fails_when_the_database_is_down(): void
    {
        DB::shouldReceive('select')->andThrow(new \RuntimeException('Database unreachable'));

        $this->get('/up')->assertServerError();
    }
}
