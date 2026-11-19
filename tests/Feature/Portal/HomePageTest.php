<?php

namespace Tests\Feature\Portal;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CmsTestSeeder;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CmsTestSeeder::class);
    }

    #[Test]
    public function home_page_returns_success(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }

    #[Test]
    public function health_endpoint_is_up(): void
    {
        $response = $this->get('/up');

        $response->assertOk();
    }
}
