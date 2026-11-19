<?php

namespace Database\Seeders;

use App\Models\Admin\AuthRule;
use App\Models\Admin\Config;
use App\Support\SiteSettings;
use Illuminate\Database\Seeder;

class CmsSiteSettingsMenuSeeder extends Seeder
{
    public function run(): void
    {
        $now = time();
        $cms = AuthRule::query()->where('name', 'cms')->first();
        if (! $cms) {
            return;
        }

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

        foreach (SiteSettings::definitions() as $name => $def) {
            $content = '';
            if ($name === 'portal_theme' && ! empty($def['options'])) {
                $content = collect($def['options'])->map(fn ($label, $k) => $k.'|'.$label)->implode("\n");
            }
            Config::query()->firstOrCreate(
                ['name' => $name],
                [
                    'group' => 'site',
                    'title' => $def['title'],
                    'type' => 'string',
                    'value' => $name === 'sitename' ? (config('app.name') ?: 'Laravel CMS') : '',
                    'visible' => '1',
                    'content' => $content,
                    'createtime' => $now,
                    'updatetime' => $now,
                ]
            );
        }
    }
}
