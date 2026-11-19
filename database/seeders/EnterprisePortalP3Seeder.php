<?php

namespace Database\Seeders;

use App\Models\Admin\Archive;
use App\Models\Admin\AuthRule;
use App\Models\Admin\Category;
use App\Models\Admin\Config;
use Illuminate\Database\Seeder;

class EnterprisePortalP3Seeder extends Seeder
{
    public function run(): void
    {
        $now = time();
        $cms = AuthRule::query()->where('name', 'cms')->first();
        if ($cms) {
            $menu = AuthRule::query()->firstOrCreate(
                ['name' => 'cms/order/index'],
                [
                    'type' => 'file',
                    'pid' => $cms->id,
                    'title' => '订单管理',
                    'icon' => 'fa fa-credit-card',
                    'ismenu' => 1,
                    'weigh' => 80,
                    'status' => 'normal',
                    'createtime' => $now,
                    'updatetime' => $now,
                ]
            );

            foreach (['edit', 'del'] as $action) {
                AuthRule::query()->firstOrCreate(
                    ['name' => 'cms/order/'.$action],
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

        foreach ([
            ['name' => 'payment_driver', 'group' => 'cms', 'title' => '默认支付方式', 'type' => 'string', 'value' => 'balance'],
            ['name' => 'vip_trial_days', 'group' => 'cms', 'title' => '演示VIP天数(0关闭)', 'type' => 'string', 'value' => '0'],
        ] as $row) {
            Config::query()->updateOrCreate(
                ['name' => $row['name']],
                array_merge($row, ['visible' => '1'])
            );
        }

        $news = Category::query()->where('nickname', 'news')->first();
        if (! $news) {
            return;
        }

        Archive::query()->updateOrCreate(
            ['slug' => 'fu-fei-yan-shi-wen-zhang'],
            [
                'category_id' => $news->id,
                'admin_id' => 1,
                'title' => '付费演示文章（P3）',
                'description' => '试读 30%，余额支付后可阅读全文并下载附件。',
                'content' => '<p>这是付费内容的公开试读部分。完整正文包含更多实施细节、清单与最佳实践。</p>'
                    .'<p>第二段：企业站变现可先采用会员余额与订单闭环，再对接微信/支付宝沙箱。</p>'
                    .'<p>第三段：付费附件可与文章同价订单一并解锁下载。</p>',
                'pay_type' => 'pay',
                'price' => 9.90,
                'pay_ratio' => 30,
                'download_url' => '/storage/cms-p3-demo.txt',
                'flag' => 'recommend',
                'weigh' => 200,
                'status' => 'normal',
                'publishtime' => $now,
                'createtime' => $now,
                'updatetime' => $now,
            ]
        );

        $demoFile = storage_path('app/public/cms-p3-demo.txt');
        if (! is_file($demoFile)) {
            if (! is_dir(dirname($demoFile))) {
                mkdir(dirname($demoFile), 0755, true);
            }
            file_put_contents($demoFile, "Laravel CMS P3 付费下载演示文件\n");
        }
    }
}
