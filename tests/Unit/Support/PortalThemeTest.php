<?php

namespace Tests\Unit\Support;

use App\Models\Admin\Config;
use App\Services\Admin\AdminCache;
use App\Support\PortalTheme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CmsTestSeeder;
use Tests\TestCase;

class PortalThemeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CmsTestSeeder::class);
    }

    #[Test]
    public function current_reads_portal_theme_config(): void
    {
        Config::query()->where('name', 'portal_theme')->update(['value' => 'cms']);
        app(AdminCache::class)->forgetConfig();

        $this->assertSame('cms', PortalTheme::current());
    }

    #[Test]
    public function view_falls_back_when_theme_view_missing(): void
    {
        Config::query()->where('name', 'portal_theme')->update(['value' => 'default']);
        app(AdminCache::class)->forgetConfig();

        $this->assertSame('portal.home', PortalTheme::view('home'));
    }
}
