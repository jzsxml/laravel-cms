<?php

namespace Database\Seeders;

use App\Addon\AddonManager;
use App\Models\Admin\AuthRule;
use App\Models\Api\Member;
use Illuminate\Database\Seeder;

class P4AdminSeeder extends Seeder
{
    public function run(): void
    {
        $now = time();
        $general = AuthRule::query()->where('name', 'general')->first();
        $pid = $general?->id ?? 0;

        foreach ([
            ['name' => 'general/addon/index', 'title' => '本地插件', 'icon' => 'fa fa-plug'],
            ['name' => 'general/command/index', 'title' => '在线命令', 'icon' => 'fa fa-terminal'],
        ] as $item) {
            $rule = AuthRule::query()->firstOrCreate(
                ['name' => $item['name']],
                [
                    'type' => 'file',
                    'pid' => $pid,
                    'title' => $item['title'],
                    'icon' => $item['icon'],
                    'ismenu' => 1,
                    'status' => 'normal',
                    'createtime' => $now,
                    'updatetime' => $now,
                ]
            );
            foreach (['install', 'enable', 'disable', 'uninstall', 'run'] as $action) {
                $actionName = str_contains($item['name'], 'addon')
                    ? 'general/addon/'.$action
                    : 'general/command/'.$action;
                AuthRule::query()->firstOrCreate(
                    ['name' => $actionName],
                    [
                        'type' => 'file',
                        'pid' => $rule->id,
                        'title' => $action,
                        'ismenu' => 0,
                        'status' => 'normal',
                        'createtime' => $now,
                        'updatetime' => $now,
                    ]
                );
            }
        }

        $uploadPid = AuthRule::query()->where('name', 'ajax/upload')->value('id') ?? $pid;
        foreach (['ajax/uploadchunk' => '分片上传', 'ajax/uploadmerge' => '合并上传'] as $ajaxRule => $title) {
            AuthRule::query()->firstOrCreate(
                ['name' => $ajaxRule],
                [
                    'type' => 'file',
                    'pid' => $uploadPid,
                    'title' => $title,
                    'ismenu' => 0,
                    'status' => 'normal',
                    'createtime' => $now,
                    'updatetime' => $now,
                ]
            );
        }

        if (! Member::query()->where('username', 'member')->exists()) {
            $salt = bin2hex(random_bytes(8));
            Member::query()->create([
                'username' => 'member',
                'nickname' => 'Demo Member',
                'password' => Member::encryptPassword('123456', $salt),
                'salt' => $salt,
                'email' => 'member@example.com',
                'status' => 'normal',
            ]);
        }

        $manager = app(AddonManager::class);
        if (is_dir(config('cms.addons.path').'/demo')) {
            try {
                $manager->install('demo');
            } catch (\Throwable) {
                // already installed
            }
        }
    }
}
