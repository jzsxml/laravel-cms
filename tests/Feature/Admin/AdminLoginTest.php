<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\CmsTestSeeder;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CmsTestSeeder::class);
    }

    #[Test]
    public function admin_login_form_is_available(): void
    {
        $adminPath = config('cms.admin_path', 'admin');
        $this->get("/{$adminPath}/login")->assertOk();
    }

    #[Test]
    public function admin_can_login_with_valid_credentials(): void
    {
        $adminPath = config('cms.admin_path', 'admin');

        $response = $this->post("/{$adminPath}/login", [
            'username' => 'admin',
            'password' => '123456',
        ]);

        $response->assertRedirect();
        $this->assertNotNull(session(config('cms.session_key', 'cms_admin')));
    }
}
