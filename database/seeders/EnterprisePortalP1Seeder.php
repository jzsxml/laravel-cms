<?php

namespace Database\Seeders;

use App\Models\Admin\Archive;
use App\Models\Admin\AuthRule;
use App\Models\Admin\CmsPage;
use App\Services\Cms\ArchiveRelations;
use Illuminate\Database\Seeder;

class EnterprisePortalP1Seeder extends Seeder
{
    public function run(): void
    {
        $now = time();
        $cms = AuthRule::query()->where('name', 'cms')->first();
        if (! $cms) {
            return;
        }

        foreach ([
            ['index' => 'cms/page/index', 'title' => '单页管理', 'icon' => 'fa fa-file-o', 'export' => true],
            ['index' => 'cms/tag/index', 'title' => '标签管理', 'icon' => 'fa fa-tags', 'export' => false],
        ] as $meta) {
            $menu = AuthRule::query()->firstOrCreate(
                ['name' => $meta['index']],
                [
                    'type' => 'file',
                    'pid' => $cms->id,
                    'title' => $meta['title'],
                    'icon' => $meta['icon'],
                    'ismenu' => 1,
                    'weigh' => 90,
                    'status' => 'normal',
                    'createtime' => $now,
                    'updatetime' => $now,
                ]
            );

            $actions = $meta['export']
                ? ['add', 'edit', 'del', 'multi', 'recyclebin', 'restore', 'destroy', 'export']
                : ['add', 'edit', 'del', 'multi'];

            foreach ($actions as $action) {
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
        }

        $pages = [
            [
                'title' => '联系我们',
                'slug' => 'lian-xi-wo-men',
                'template' => 'contact',
                'flag' => 'nav',
                'content' => '<p>电话：400-000-0000</p><p>邮箱：contact@example.com</p><p>欢迎留言洽谈合作。</p>',
            ],
            [
                'title' => '服务协议',
                'slug' => 'fu-wu-xie-yi',
                'template' => 'default',
                'flag' => '',
                'content' => '<p>请在后台完善服务协议与隐私条款正文。</p>',
            ],
        ];

        foreach ($pages as $row) {
            CmsPage::query()->firstOrCreate(
                ['slug' => $row['slug']],
                [
                    'title' => $row['title'],
                    'template' => $row['template'],
                    'flag' => $row['flag'],
                    'content' => $row['content'],
                    'status' => 'normal',
                    'weigh' => 50,
                ]
            );
        }

        $news = Archive::query()->where('title', 'like', '%上线公告%')->first();
        if ($news) {
            app(ArchiveRelations::class)->syncTags($news, '公告,企业站,CMS');
        }
    }
}
