<?php

namespace Database\Seeders;

use App\Models\Admin\Archive;
use App\Models\Admin\AuthRule;
use App\Models\Admin\CmsBlock;
use App\Models\Admin\Config;
use App\Models\Admin\Special;
use Illuminate\Database\Seeder;

class EnterprisePortalP2Seeder extends Seeder
{
    public function run(): void
    {
        $now = time();
        $cms = AuthRule::query()->where('name', 'cms')->first();
        if (! $cms) {
            return;
        }

        foreach ([
            [
                'index' => 'cms/comment/index',
                'title' => '评论管理',
                'icon' => 'fa fa-comments',
                'actions' => ['edit', 'del', 'multi', 'recyclebin', 'restore', 'destroy'],
            ],
            [
                'index' => 'cms/special/index',
                'title' => '专题管理',
                'icon' => 'fa fa-bookmark',
                'actions' => ['add', 'edit', 'del', 'multi', 'recyclebin', 'restore', 'destroy', 'export'],
                'extra' => [['name' => 'cms/special/batch-archives', 'title' => '关联文章']],
            ],
            [
                'index' => 'cms/block/index',
                'title' => '区块管理',
                'icon' => 'fa fa-th-large',
                'actions' => ['add', 'edit', 'del', 'multi'],
            ],
        ] as $meta) {
            $menu = AuthRule::query()->firstOrCreate(
                ['name' => $meta['index']],
                [
                    'type' => 'file',
                    'pid' => $cms->id,
                    'title' => $meta['title'],
                    'icon' => $meta['icon'],
                    'ismenu' => 1,
                    'weigh' => 85,
                    'status' => 'normal',
                    'createtime' => $now,
                    'updatetime' => $now,
                ]
            );

            foreach ($meta['actions'] as $action) {
                AuthRule::query()->firstOrCreate(
                    ['name' => str_replace('/index', '/'.$action, $meta['index'])],
                    [
                        'type' => 'file',
                        'pid' => $menu->id,
                        'title' => $action,
                        'ismenu' => 0,
                        'status' => 'normal',
                        'createtime' => $now,
                        'updatetime' => $now,
                    ]
                );
            }

            foreach ($meta['extra'] ?? [] as $rule) {
                AuthRule::query()->firstOrCreate(
                    ['name' => $rule['name']],
                    [
                        'type' => 'file',
                        'pid' => $menu->id,
                        'title' => $rule['title'],
                        'ismenu' => 0,
                        'status' => 'normal',
                        'createtime' => $now,
                        'updatetime' => $now,
                    ]
                );
            }
        }

        $archivesMenu = AuthRule::query()->where('name', 'cms/archives/index')->first();
        if ($archivesMenu) {
            foreach ([
                'approve' => '审核通过',
                'reject' => '拒稿',
                'batch-special' => '批量专题',
                'batch-tags' => '批量标签',
            ] as $action => $title) {
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
        }

        foreach ([
            ['name' => 'contribute_category_ids', 'group' => 'cms', 'title' => '允许投稿的栏目昵称', 'type' => 'string', 'value' => 'news,products,cases'],
            ['name' => 'comment_notify_email', 'group' => 'cms', 'title' => '评论通知邮箱', 'type' => 'string', 'value' => ''],
        ] as $row) {
            Config::query()->updateOrCreate(
                ['name' => $row['name']],
                array_merge($row, ['visible' => '1'])
            );
        }

        CmsBlock::query()->firstOrCreate(
            ['name' => 'home_banner'],
            [
                'title' => '首页横幅',
                'type' => 'html',
                'content' => '<div class="alert alert-info mb-0">欢迎访问企业门户 — 可在后台 <strong>内容管理 → 区块管理</strong> 编辑 <code>home_banner</code>。</div>',
                'status' => 'normal',
                'weigh' => 100,
            ]
        );

        $special = Special::query()->firstOrCreate(
            ['slug' => 'jing-xuan'],
            [
                'title' => '精选专题',
                'description' => '推荐与置顶内容合集',
                'content' => '<p>本专题聚合站点推荐内容，可按标签筛选。</p>',
                'status' => 'normal',
                'weigh' => 100,
            ]
        );

        $archiveIds = Archive::query()
            ->where('status', 'normal')
            ->whereNull('deletetime')
            ->orderByDesc('weigh')
            ->limit(6)
            ->pluck('id')
            ->all();

        if ($archiveIds !== []) {
            $special->archives()->syncWithoutDetaching($archiveIds);
        }
    }
}
