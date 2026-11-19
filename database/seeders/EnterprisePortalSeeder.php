<?php

namespace Database\Seeders;

use App\Models\Admin\Archive;
use App\Models\Admin\AuthRule;
use App\Models\Admin\Category;
use App\Models\Admin\Config;
use App\Support\ArchiveSlug;
use Illuminate\Database\Seeder;

class EnterprisePortalSeeder extends Seeder
{
    public function run(): void
    {
        $now = time();

        $dict = Config::query()->where('name', 'categorytype')->first();
        if ($dict) {
            $content = $dict->content ?? '';
            if (! str_contains($content, 'article|')) {
                $dict->content = trim($content)."\narticle|内容栏目";
                $dict->save();
            }
        }

        $siteConfigs = [
            ['name' => 'site_logo', 'group' => 'site', 'title' => '站点 Logo URL', 'type' => 'string', 'value' => ''],
            ['name' => 'site_slogan', 'group' => 'site', 'title' => '站点标语', 'type' => 'string', 'value' => '专注企业数字化服务'],
            ['name' => 'site_hero_image', 'group' => 'site', 'title' => '首页大图 URL', 'type' => 'string', 'value' => ''],
            ['name' => 'site_contact_phone', 'group' => 'site', 'title' => '联系电话', 'type' => 'string', 'value' => '400-000-0000'],
            ['name' => 'site_contact_email', 'group' => 'site', 'title' => '联系邮箱', 'type' => 'string', 'value' => 'contact@example.com'],
            ['name' => 'site_icp', 'group' => 'site', 'title' => 'ICP 备案号', 'type' => 'string', 'value' => ''],
        ];
        foreach ($siteConfigs as $row) {
            Config::query()->updateOrCreate(
                ['name' => $row['name']],
                array_merge($row, ['visible' => '1'])
            );
        }

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

        $archivesMenu = AuthRule::query()->firstOrCreate(
            ['name' => 'cms/archives/index'],
            [
                'type' => 'file',
                'pid' => $cms->id,
                'title' => '文章管理',
                'icon' => 'fa fa-file-text',
                'ismenu' => 1,
                'weigh' => 99,
                'status' => 'normal',
                'createtime' => $now,
                'updatetime' => $now,
            ]
        );

        foreach (['add' => '添加', 'edit' => '编辑', 'del' => '删除', 'multi' => '批量', 'recyclebin' => '回收站', 'restore' => '还原', 'destroy' => '销毁', 'export' => '导出'] as $action => $title) {
            AuthRule::query()->firstOrCreate(
                ['name' => 'cms/archives/'.$action],
                [
                    'type' => 'file',
                    'pid' => $archivesMenu->id,
                    'title' => $title,
                    'ismenu' => 0,
                    'status' => 'normal',
                    'createtime' => $now,
                    'updatetime' => $now,
                ]
            );
        }

        $categories = [
            ['name' => '新闻动态', 'nickname' => 'news', 'weigh' => 100],
            ['name' => '产品中心', 'nickname' => 'products', 'weigh' => 90],
            ['name' => '客户案例', 'nickname' => 'cases', 'weigh' => 80],
            ['name' => '公司简介', 'nickname' => 'about', 'weigh' => 70],
        ];

        foreach ($categories as $cat) {
            Category::query()->firstOrCreate(
                ['nickname' => $cat['nickname']],
                [
                    'pid' => 0,
                    'type' => 'article',
                    'name' => $cat['name'],
                    'status' => 'normal',
                    'weigh' => $cat['weigh'],
                    'createtime' => $now,
                    'updatetime' => $now,
                ]
            );
        }

        $about = Category::query()->where('nickname', 'about')->first();
        if ($about && ! Archive::query()->where('slug', 'guan-yu-wo-men')->exists()) {
            $title = '关于我们';
            Archive::query()->create([
                'category_id' => $about->id,
                'admin_id' => 1,
                'slug' => ArchiveSlug::fromTitle($title),
                'title' => $title,
                'description' => '欢迎了解我们的企业与服务。',
                'content' => '<p>请在后台编辑本文，完善公司介绍。</p>',
                'flag' => 'recommend',
                'weigh' => 100,
                'status' => 'normal',
                'publishtime' => $now,
            ]);
        }

        $news = Category::query()->where('nickname', 'news')->first();
        if ($news && ! Archive::query()->where('title', '企业站 P0 上线公告')->exists()) {
            Archive::query()->create([
                'category_id' => $news->id,
                'admin_id' => 1,
                'slug' => ArchiveSlug::fromTitle('企业站 P0 上线公告'),
                'title' => '企业站 P0 上线公告',
                'description' => '内容管理 P0 已启用，支持拼音 Slug 与企业门户展示。',
                'content' => '<p>您可在管理后台 <strong>内容管理 → 文章管理</strong> 中维护本站内容。</p>',
                'flag' => 'top,recommend',
                'status' => 'normal',
                'publishtime' => $now,
            ]);
        }
    }
}
