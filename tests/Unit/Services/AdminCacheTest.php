<?php

namespace Tests\Unit\Services;

use App\Models\Admin\Config;
use App\Services\Admin\AdminCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CmsTestSeeder;
use Tests\TestCase;

class AdminCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CmsTestSeeder::class);
    }

    #[Test]
    public function config_value_is_cached_until_forget(): void
    {
        $cache = app(AdminCache::class);
        Config::query()->where('name', 'sitename')->update(['value' => '缓存前']);

        $this->assertSame('缓存前', $cache->configValue('sitename'));

        Config::query()->where('name', 'sitename')->update(['value' => 'DB已改']);
        $this->assertSame('缓存前', $cache->configValue('sitename'));

        $cache->forgetConfig();
        $this->assertSame('DB已改', $cache->configValue('sitename'));
    }

    #[Test]
    public function refresh_rebuilds_meta(): void
    {
        $cache = app(AdminCache::class);
        $meta = $cache->refresh();

        $this->assertArrayHasKey('refreshed_at', $meta);
        $this->assertNotEmpty($cache->configMap());
    }
}
