<?php

namespace Database\Seeders;

use App\Models\Admin\AuthRule;
use Illuminate\Database\Seeder;

class ProductionGapSeeder extends Seeder
{
    public function run(): void
    {
        $now = time();
        $auth = AuthRule::query()->where('name', 'auth')->first();
        $pid = $auth?->id ?? 0;

        $menu = AuthRule::query()->firstOrCreate(
            ['name' => 'auth/admin/index'],
            [
                'type' => 'file',
                'pid' => $pid,
                'title' => '管理员管理',
                'icon' => 'fa fa-user-secret',
                'ismenu' => 1,
                'weigh' => 95,
                'status' => 'normal',
                'createtime' => $now,
                'updatetime' => $now,
            ]
        );

        foreach (['add' => '添加', 'edit' => '编辑', 'del' => '删除'] as $action => $title) {
            AuthRule::query()->firstOrCreate(
                ['name' => 'auth/admin/'.$action],
                [
                    'type' => 'file',
                    'pid' => $menu->id,
                    'title' => $title,
                    'ismenu' => 0,
                    'status' => 'normal',
                    'createtime' => $now,
                    'updatetime' => $now,
                ]
            );
        }

        $general = AuthRule::query()->where('name', 'general')->first();
        $gpid = $general?->id ?? 0;

        foreach ([
            ['name' => 'general/apidoc/index', 'title' => 'API 文档', 'icon' => 'fa fa-book', 'weigh' => 50],
            ['name' => 'general/sms/index', 'title' => '短信测试', 'icon' => 'fa fa-commenting', 'weigh' => 48],
        ] as $item) {
            AuthRule::query()->firstOrCreate(
                ['name' => $item['name']],
                [
                    'type' => 'file',
                    'pid' => $gpid,
                    'title' => $item['title'],
                    'icon' => $item['icon'],
                    'ismenu' => 1,
                    'weigh' => $item['weigh'],
                    'status' => 'normal',
                    'createtime' => $now,
                    'updatetime' => $now,
                ]
            );
        }

        $smsMenu = AuthRule::query()->where('name', 'general/sms/index')->first();
        if ($smsMenu) {
            AuthRule::query()->firstOrCreate(
                ['name' => 'general/sms/send'],
                [
                    'type' => 'file',
                    'pid' => $smsMenu->id,
                    'title' => '发送',
                    'ismenu' => 0,
                    'status' => 'normal',
                    'createtime' => $now,
                    'updatetime' => $now,
                ]
            );
        }
    }
}
