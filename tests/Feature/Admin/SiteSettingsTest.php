<?php

namespace Tests\Feature\Admin;

use App\Services\Admin\AdminCache;
use App\Support\SiteConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\ActsAsAdmin;
use Tests\Support\CmsTestSeeder;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use ActsAsAdmin;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CmsTestSeeder::class);
    }

    #[Test]
    public function guest_cannot_access_site_settings(): void
    {
        $adminPath = config('cms.admin_path', 'admin');
        $this->get("/{$adminPath}/cms/site")->assertRedirect();
    }

    #[Test]
    public function admin_can_save_site_settings(): void
    {
        $this->loginAsAdmin();
        $adminPath = config('cms.admin_path', 'admin');

        $response = $this->post("/{$adminPath}/cms/site", [
            'sitename' => '功能测试站',
            'site_slogan' => '标语',
            'site_logo' => '',
            'site_hero_image' => '',
            'site_contact_phone' => '400-123-4567',
            'site_contact_email' => 'admin@test.local',
            'site_icp' => '测试ICP',
            'portal_theme' => 'default',
        ]);

        $response->assertRedirect();
        app(AdminCache::class)->forgetConfig();
        $this->assertSame('功能测试站', SiteConfig::get('sitename'));
        $this->assertSame('400-123-4567', SiteConfig::get('site_contact_phone'));
    }
}
