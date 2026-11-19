<?php

namespace Database\Seeders;

use App\Crud\MenuPublisher;
use App\Models\Admin\AuthRule;
use Illuminate\Database\Seeder;

class FeatureMenusSeeder extends Seeder
{
    public function run(): void
    {
        $now = time();
        $general = AuthRule::query()->where('name', 'general')->first();
        $gpid = $general?->id ?? 0;

        $configMenu = AuthRule::query()->where('name', 'general/config/index')->first();
        $cpid = $configMenu?->id ?? $gpid;
        foreach (['add', 'edit', 'del'] as $action) {
            AuthRule::query()->firstOrCreate(
                ['name' => 'general/config/'.$action],
                [
                    'type' => 'file',
                    'pid' => $cpid,
                    'title' => match ($action) {
                        'add' => '添加',
                        'edit' => '编辑',
                        'del' => '删除',
                        default => $action,
                    },
                    'ismenu' => 0,
                    'status' => 'normal',
                    'createtime' => $now,
                    'updatetime' => $now,
                ]
            );
        }

        $cacheMenu = AuthRule::query()->firstOrCreate(
            ['name' => 'general/cache/index'],
            [
                'type' => 'file',
                'pid' => $gpid,
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
                'pid' => $cacheMenu->id,
                'title' => '刷新',
                'ismenu' => 0,
                'status' => 'normal',
                'createtime' => $now,
                'updatetime' => $now,
            ]
        );

        $attachment = AuthRule::query()->where('name', 'general/attachment/index')->first();
        AuthRule::query()->firstOrCreate(
            ['name' => 'general/attachment/del'],
            [
                'type' => 'file',
                'pid' => $attachment?->id ?? $gpid,
                'title' => '删除',
                'ismenu' => 0,
                'status' => 'normal',
                'createtime' => $now,
                'updatetime' => $now,
            ]
        );

        AuthRule::query()->firstOrCreate(
            ['name' => 'general/profile/index'],
            [
                'type' => 'file',
                'pid' => $gpid,
                'title' => '个人资料',
                'icon' => 'fa fa-user',
                'ismenu' => 0,
                'status' => 'normal',
                'createtime' => $now,
                'updatetime' => $now,
            ]
        );

        $memberMenu = AuthRule::query()->firstOrCreate(
            ['name' => 'general/member/index'],
            [
                'type' => 'file',
                'pid' => $gpid,
                'title' => '会员管理',
                'icon' => 'fa fa-user-friends',
                'ismenu' => 1,
                'weigh' => 5,
                'status' => 'normal',
                'createtime' => $now,
                'updatetime' => $now,
            ]
        );

        foreach (['add', 'edit', 'del', 'multi'] as $action) {
            AuthRule::query()->firstOrCreate(
                ['name' => 'general/member/'.$action],
                [
                    'type' => 'file',
                    'pid' => $memberMenu->id,
                    'title' => $action,
                    'ismenu' => 0,
                    'status' => 'normal',
                    'createtime' => $now,
                    'updatetime' => $now,
                ]
            );
        }

        $publisher = app(MenuPublisher::class);
        $publisher->publish('test', '测试', ['index', 'add', 'edit', 'del', 'multi', 'recyclebin', 'restore', 'destroy'], true);

        $ruleMenu = AuthRule::query()->where('name', 'auth/rule/index')->first();
        $rpid = $ruleMenu?->id ?? 0;
        foreach (['add', 'edit', 'del'] as $action) {
            AuthRule::query()->firstOrCreate(
                ['name' => 'auth/rule/'.$action],
                [
                    'type' => 'file',
                    'pid' => $rpid,
                    'title' => match ($action) {
                        'add' => '添加',
                        'edit' => '编辑',
                        'del' => '删除',
                        default => $action,
                    },
                    'ismenu' => 0,
                    'status' => 'normal',
                    'createtime' => $now,
                    'updatetime' => $now,
                ]
            );
        }

        $groupMenu = AuthRule::query()->where('name', 'auth/group/index')->first();
        $gpid = $groupMenu?->id ?? 0;
        foreach (['add', 'edit', 'del'] as $action) {
            AuthRule::query()->firstOrCreate(
                ['name' => 'auth/group/'.$action],
                [
                    'type' => 'file',
                    'pid' => $gpid,
                    'title' => match ($action) {
                        'add' => '添加',
                        'edit' => '编辑',
                        'del' => '删除',
                        default => $action,
                    },
                    'ismenu' => 0,
                    'status' => 'normal',
                    'createtime' => $now,
                    'updatetime' => $now,
                ]
            );
        }
    }
}
