<?php

namespace Database\Seeders;

use App\Models\Admin\Admin;
use App\Models\Admin\AuthRule;
use App\Models\Admin\Category;
use App\Models\Admin\Test;
use Illuminate\Database\Seeder;

class TestAdminSeeder extends Seeder
{
    public function run(): void
    {
        AuthRule::query()->where('name', 'test/index')->update([
            'icon' => 'fa fa-flask',
            'title' => '测试管理',
        ]);

        $admin = Admin::query()->where('username', 'admin')->first();
        $category = Category::query()->where('type', 'test')->orderBy('id')->first();

        if (! $category || Test::query()->exists()) {
            return;
        }

        Test::query()->create([
            'admin_id' => $admin?->id ?? 0,
            'category_id' => $category->id,
            'title' => '示例测试数据',
            'image' => '',
            'images' => '',
            'weigh' => 100,
            'switch' => 1,
            'status' => 'normal',
            'content' => '<p>这是一条用于演示 CRUD、回收站、批量操作与富文本的测试数据。</p>',
        ]);
    }
}
