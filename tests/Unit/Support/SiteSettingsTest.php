<?php

namespace Tests\Unit\Support;

use App\Models\Admin\Config;
use App\Support\SiteConfig;
use App\Support\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CmsTestSeeder;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CmsTestSeeder::class);
    }

    #[Test]
    public function definitions_include_core_portal_keys(): void
    {
        $defs = SiteSettings::definitions();
        $this->assertArrayHasKey('sitename', $defs);
        $this->assertArrayHasKey('site_icp', $defs);
        $this->assertArrayHasKey('portal_theme', $defs);
    }

    #[Test]
    public function save_persists_to_config_and_site_config_reads_it(): void
    {
        SiteSettings::save([
            'sitename' => '单元测试站',
            'site_slogan' => '标语',
            'site_logo' => '',
            'site_hero_image' => '',
            'site_contact_phone' => '10086',
            'site_contact_email' => 'test@example.com',
            'site_icp' => '测ICP备000',
            'portal_theme' => 'cms',
        ]);

        $this->assertSame('单元测试站', SiteConfig::get('sitename'));
        $this->assertSame('10086', SiteConfig::get('site_contact_phone'));
        $this->assertDatabaseHas(Config::query()->getModel()->getTable(), [
            'name' => 'sitename',
            'value' => '单元测试站',
        ]);
    }
}
