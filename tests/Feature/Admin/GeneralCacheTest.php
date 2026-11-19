<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\ActsAsAdmin;
use Tests\Support\CmsTestSeeder;
use Tests\TestCase;

class GeneralCacheTest extends TestCase
{
    use ActsAsAdmin;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CmsTestSeeder::class);
    }

    #[Test]
    public function admin_can_refresh_cache_via_api(): void
    {
        $this->loginAsAdmin();
        $adminPath = config('cms.admin_path', 'admin');

        $response = $this->postJson("/{$adminPath}/general/cache/refresh");

        $response->assertOk()
            ->assertJsonPath('code', 1);
    }
}
