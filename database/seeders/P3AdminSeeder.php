<?php

namespace Database\Seeders;

use App\Models\Admin\AuthRule;
use App\Models\Admin\Category;
use App\Models\Admin\Config;
use Illuminate\Database\Seeder;

class P3AdminSeeder extends Seeder
{
    public function run(): void
    {
        $now = time();

        Config::query()->updateOrCreate(
            ['name' => 'categorytype'],
            [
                'group' => 'dictionary',
                'title' => '分类类型',
                'type' => 'array',
                'content' => "test|测试模块\ndefault|默认分类",
                'value' => '',
            ]
        );

        $general = AuthRule::query()->where('name', 'general')->first();
        $pid = $general?->id ?? 0;

        AuthRule::query()->firstOrCreate(
            ['name' => 'general/category/index'],
            [
                'type' => 'file',
                'pid' => $pid,
                'title' => '分类管理',
                'icon' => 'fa fa-list',
                'ismenu' => 1,
                'status' => 'normal',
                'createtime' => $now,
                'updatetime' => $now,
            ]
        );

        foreach (['add', 'edit', 'del', 'multi'] as $action) {
            $parent = AuthRule::query()->where('name', 'general/category/index')->first();
            AuthRule::query()->firstOrCreate(
                ['name' => 'general/category/'.$action],
                [
                    'type' => 'file',
                    'pid' => $parent?->id ?? 0,
                    'title' => $action,
                    'ismenu' => 0,
                    'status' => 'normal',
                    'createtime' => $now,
                    'updatetime' => $now,
                ]
            );
        }

        AuthRule::query()->firstOrCreate(
            ['name' => 'ajax/selectpage'],
            [
                'type' => 'file',
                'pid' => $pid,
                'title' => 'Selectpage',
                'ismenu' => 0,
                'status' => 'normal',
                'createtime' => $now,
                'updatetime' => $now,
            ]
        );

        if (Category::query()->count() === 0) {
            $parent = Category::query()->create([
                'type' => 'test',
                'name' => '测试父级',
                'weigh' => 100,
                'status' => 'normal',
            ]);
            Category::query()->create([
                'pid' => $parent->id,
                'type' => 'test',
                'name' => '测试子级',
                'weigh' => 50,
                'status' => 'normal',
            ]);
        }
    }
}
