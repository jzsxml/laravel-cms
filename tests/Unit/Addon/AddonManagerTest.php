<?php

namespace Tests\Unit\Addon;

use App\Addon\AddonManager;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AddonManagerTest extends TestCase
{
    #[Test]
    public function enable_and_disable_updates_manifest(): void
    {
        if (! File::isDirectory(config('cms.addons.path').'/demo')) {
            $this->markTestSkipped('addons/demo not present');
        }

        $manager = app(AddonManager::class);
        $enabledPath = config('cms.addons.enabled');

        $manager->disable('demo');
        $this->assertNotContains('demo', $manager->enabledNames());

        $manager->enable('demo');
        $this->assertContains('demo', $manager->enabledNames());
        $this->assertFileExists($enabledPath);

        $manager->disable('demo');
    }

    #[Test]
    public function enable_throws_for_missing_addon(): void
    {
        $this->expectException(\RuntimeException::class);
        app(AddonManager::class)->enable('nonexistent-addon-xyz');
    }
}
