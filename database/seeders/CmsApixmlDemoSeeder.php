<?php

namespace Database\Seeders;

use App\Models\Admin\Archive;
use App\Models\Admin\Category;
use App\Models\Admin\CmsBlock;
use App\Models\Admin\Config;
use App\Models\Admin\Special;
use App\Models\Admin\Tag;
use App\Services\Admin\AdminCache;
use App\Support\ArchiveSlug;
use Illuminate\Database\Seeder;

class CmsApixmlDemoSeeder extends Seeder
{
    public function run(): void
    {
        $now = time() - 86400 * 200;

        $this->hideLegacyDuplicateNavCategories();

        foreach ([
            'sitename' => '我的CMS网站',
            'site_slogan' => '内容管理系统演示门户',
            'portal_theme' => 'cms',
            'site_icp' => '鲁ICP备2025204892号（演示）',
        ] as $name => $value) {
            Config::query()->updateOrCreate(
                ['name' => $name],
                [
                    'group' => 'site',
                    'title' => $name,
                    'type' => 'string',
                    'value' => $value,
                    'visible' => '1',
                ]
            );
        }

        $parents = [
            ['name' => '新闻中心', 'nickname' => 'news-center', 'weigh' => 100, 'children' => [
                ['name' => '硬件', 'nickname' => 'hardware'],
                ['name' => '互联网', 'nickname' => 'internet'],
            ]],
            ['name' => '产品中心', 'nickname' => 'products-center', 'weigh' => 90, 'children' => [
                ['name' => '智能家居', 'nickname' => 'smart-home'],
                ['name' => '移动设备', 'nickname' => 'mobile-device'],
                ['name' => '智能设备', 'nickname' => 'smart-device'],
            ]],
            ['name' => '下载中心', 'nickname' => 'download-center', 'weigh' => 80, 'children' => [
                ['name' => '网络工具', 'nickname' => 'network-tools'],
                ['name' => '媒体工具', 'nickname' => 'media-tools'],
                ['name' => '应用软件', 'nickname' => 'app-soft'],
            ]],
        ];

        $categoryIds = [];
        foreach ($parents as $p) {
            $parent = Category::query()->firstOrCreate(
                ['nickname' => $p['nickname']],
                [
                    'pid' => 0,
                    'type' => 'article',
                    'name' => $p['name'],
                    'status' => 'normal',
                    'weigh' => $p['weigh'],
                    'createtime' => $now,
                    'updatetime' => $now,
                ]
            );
            $categoryIds[$p['nickname']] = (int) $parent->id;
            foreach ($p['children'] as $i => $child) {
                $row = Category::query()->firstOrCreate(
                    ['nickname' => $child['nickname']],
                    [
                        'pid' => $parent->id,
                        'type' => 'article',
                        'name' => $child['name'],
                        'status' => 'normal',
                        'weigh' => 50 - $i,
                        'createtime' => $now,
                        'updatetime' => $now,
                    ]
                );
                $categoryIds[$child['nickname']] = (int) $row->id;
            }
        }

        $articles = [
            [
                'title' => '奥睿科ORICO HU3温室加湿器',
                'cat' => 'smart-device',
                'desc' => '加湿器可以说是目前家里比较常见的生活用品之一了，特别是爱美的女性朋友们，为了能保持容颜润泽，经常会在身旁备用一台。',
                'flag' => 'focus,recommend',
                'views' => 6125,
                'likes' => 168,
            ],
            [
                'title' => 'Leap Motion AR头显看起来很疯狂但可能是奇迹',
                'cat' => 'hardware',
                'desc' => '本周我们正在研究Leap Motion为增强现实带来的新东西，运动跟踪和控制面向 AR 现实世界。',
                'flag' => 'focus,recommend',
                'views' => 7111,
                'likes' => 70,
            ],
            [
                'title' => '米家互联网洗烘一体机',
                'cat' => 'smart-device',
                'desc' => '米家互联网洗烘一体机10kg版采用BLDC变频电机、1400高转速、一级能效、以及包含智能空气洗在内的21种洗涤模式。',
                'flag' => 'focus,recommend',
                'views' => 2742,
                'likes' => 143,
            ],
            [
                'title' => '米家无线投影仪青春版',
                'cat' => 'smart-device',
                'desc' => '从2017年推出首款激光电视以来，小米正式扎根智能微投领域。',
                'flag' => 'recommend',
                'views' => 482,
                'likes' => 83,
            ],
            [
                'title' => '智能游戏WIFI无线手柄',
                'cat' => 'smart-home',
                'desc' => '游戏手柄除了可以在电脑上进行有线连接以外，很多厂商也设计出了无线连接的手柄。',
                'flag' => 'recommend',
                'views' => 5770,
                'likes' => 296,
            ],
            [
                'title' => '高保真无绳WIFI耳机',
                'cat' => 'mobile-device',
                'desc' => '在蓝牙4.2时代，续航、延迟与偶尔的断线是真无线耳机无法完全解决的问题。',
                'flag' => 'recommend',
                'views' => 4038,
                'likes' => 285,
            ],
            [
                'title' => 'Royal TSX 1.4.6 好用的多终端工具',
                'cat' => 'network-tools',
                'desc' => 'Royal TSX专为服务器管理员、系统工程师、开发人员和IT信息工作者开发设计。',
                'flag' => '',
                'views' => 1123,
                'likes' => 102,
                'download_url' => 'https://example.com/downloads/royaltsx',
            ],
            [
                'title' => 'vSSH 1.11.1 强大的多标签ssh工具',
                'cat' => 'network-tools',
                'desc' => 'Vssh for Mac是一款mac平台的SSH客户端工具，支持用户名和密码加密。',
                'flag' => '',
                'views' => 6229,
                'likes' => 117,
                'download_url' => 'https://example.com/downloads/vssh',
            ],
            [
                'title' => 'Samsung Galaxy 智能手机',
                'cat' => 'mobile-device',
                'desc' => '热门图集示例：智能手机产品线。',
                'flag' => 'recommend',
                'views' => 3200,
                'likes' => 90,
            ],
            [
                'title' => 'Macbook Pro Retina 笔记本',
                'cat' => 'hardware',
                'desc' => '热门图集示例：高性能笔记本。',
                'flag' => 'recommend',
                'views' => 4100,
                'likes' => 120,
            ],
        ];

        $tagNames = ['智能设备', '互联网', 'Mac', 'AR', 'WiFi', '视频', 'Google', '安全'];
        $tags = [];
        foreach ($tagNames as $tname) {
            $slug = ArchiveSlug::fromTitle($tname);
            $tags[$tname] = Tag::query()->firstOrCreate(
                ['slug' => $slug],
                ['name' => $tname, 'weigh' => 10, 'createtime' => $now, 'updatetime' => $now]
            );
        }

        $i = 0;
        foreach ($articles as $row) {
            $i++;
            $slug = ArchiveSlug::fromTitle($row['title']);
            if (Archive::query()->where('slug', $slug)->exists()) {
                continue;
            }
            $cid = $categoryIds[$row['cat']] ?? $categoryIds['smart-device'];
            $archive = Archive::query()->create([
                'category_id' => $cid,
                'admin_id' => 1,
                'slug' => $slug,
                'title' => $row['title'],
                'description' => $row['desc'],
                'content' => '<p>'.e($row['desc']).'</p><p>本文为演示数据，可在后台 <strong>内容管理 → 文章管理</strong> 中编辑。</p>',
                'image' => 'https://picsum.photos/seed/cms'.$i.'/800/500',
                'flag' => $row['flag'],
                'weigh' => 100 - $i,
                'views' => $row['views'],
                'likes' => $row['likes'],
                'download_url' => $row['download_url'] ?? '',
                'status' => 'normal',
                'publishtime' => $now + $i * 3600,
            ]);
            $archive->tags()->sync(array_slice(array_values($tags), 0, 3));
        }

        CmsBlock::query()->firstOrCreate(
            ['name' => 'friend_links'],
            [
                'title' => '友情链接',
                'type' => 'html',
                'content' => '<a href="https://www.baidu.com" target="_blank" rel="noopener" class="me-2">百度</a><a href="https://www.taobao.com" target="_blank" rel="noopener">淘宝</a>',
                'weigh' => 0,
                'status' => 'normal',
                'createtime' => $now,
                'updatetime' => $now,
            ]
        );

        Special::query()->firstOrCreate(
            ['slug' => 'demo-special'],
            [
                'title' => '科技专题',
                'image' => 'https://picsum.photos/seed/special/800/300',
                'description' => '演示专题页',
                'content' => '<p>专题内容演示</p>',
                'weigh' => 10,
                'status' => 'normal',
                'createtime' => $now,
                'updatetime' => $now,
            ]
        );

        app(AdminCache::class)->forgetCategories();
    }

    /**
     * 企业门户种子栏目（news/products）与 CMS 演示栏目（news-center/products-center）并存时会顶栏重复。
     */
    protected function hideLegacyDuplicateNavCategories(): void
    {
        $now = time();
        Category::query()
            ->where('pid', 0)
            ->whereIn('nickname', ['news', 'products'])
            ->update(['status' => 'hidden', 'updatetime' => $now]);
    }
}
