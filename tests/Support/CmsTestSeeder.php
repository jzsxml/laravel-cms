<?php

namespace Tests\Support;

use App\Models\Admin\AuthRule;
use App\Models\Admin\Config;
use App\Support\SiteSettings;
use Database\Seeders\CmsAdminSeeder;
use Illuminate\Database\Seeder;

class CmsTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CmsAdminSeeder::class);

        $now = time();
        $cms = AuthRule::query()->firstOrCreate(
            ['name' => 'cms'],
            [
                'type' => 'menu',
                'pid' => 0,
                'title' => '内容管理',
                'icon' => 'fa fa-newspaper',
                'ismenu' => 1,
                'weigh' => 88,
                'status' => 'normal',
                'createtime' => $now,
                'updatetime' => $now,
            ]
        );

        AuthRule::query()->firstOrCreate(
            ['name' => 'cms/site/index'],
            [
                'type' => 'file',
                'pid' => $cms->id,
                'title' => '站点配置',
                'icon' => 'fa fa-globe',
                'ismenu' => 1,
                'weigh' => 98,
                'status' => 'normal',
                'createtime' => $now,
                'updatetime' => $now,
            ]
        );

        $general = AuthRule::query()->where('name', 'general')->first();
        if ($general) {
            AuthRule::query()->firstOrCreate(
                ['name' => 'general/cache/index'],
                [
                    'type' => 'file',
                    'pid' => $general->id,
                    'title' => '更新缓存',
                    'icon' => 'fa fa-sync',
                    'ismenu' => 1,
                    'weigh' => 9,
                    'status' => 'normal',
                    'createtime' => $now,
                    'updatetime' => $now,
                ]
            );
            AuthRule::query()->firstOrCreate(
                ['name' => 'general/cache/refresh'],
                [
                    'type' => 'file',
                    'pid' => AuthRule::query()->where('name', 'general/cache/index')->value('id'),
                    'title' => '刷新',
                    'ismenu' => 0,
                    'status' => 'normal',
                    'createtime' => $now,
                    'updatetime' => $now,
                ]
            );
        }

        foreach (SiteSettings::definitions() as $name => $def) {
            Config::query()->firstOrCreate(
                ['name' => $name],
                [
                    'group' => 'site',
                    'title' => $def['title'],
                    'type' => 'string',
                    'value' => $name === 'sitename' ? '测试站点' : ($name === 'portal_theme' ? 'default' : ''),
                    'visible' => '1',
                ]
            );
        }
    }
}
