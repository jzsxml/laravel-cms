<?php

namespace Tests\Unit\Services;

use App\Models\Admin\AuthGroup;
use App\Models\Admin\AuthRule;
use App\Services\Admin\AdminAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\ActsAsAdmin;
use Tests\Support\CmsTestSeeder;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use ActsAsAdmin;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CmsTestSeeder::class);
    }

    #[Test]
    public function super_admin_has_wildcard_permission(): void
    {
        $this->loginAsAdmin();
        $auth = app(AdminAuth::class);

        $this->assertTrue($auth->check('cms/site/index'));
        $this->assertTrue($auth->check('anything/unknown'));
    }

    #[Test]
    public function limited_group_with_menu_rule_can_access_child_action(): void
    {
        $menuRuleId = AuthRule::query()->where('name', 'cms/site/index')->value('id');
        $this->assertNotNull($menuRuleId);

        $group = AuthGroup::query()->firstOrFail();
        $group->update(['rules' => (string) $menuRuleId]);

        $this->loginAsAdmin();
        $auth = app(AdminAuth::class);

        $this->assertTrue($auth->check('cms/site/index'));
    }
}
