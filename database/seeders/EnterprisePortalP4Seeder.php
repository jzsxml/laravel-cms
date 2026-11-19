<?php

namespace Database\Seeders;

use App\Addon\AddonManager;
use App\Models\Admin\AuthRule;
use App\Models\Admin\CmsModel;
use App\Models\Admin\CmsModelData;
use App\Models\Admin\Config;
use Illuminate\Database\Seeder;

class EnterprisePortalP4Seeder extends Seeder
{
    public function run(): void
    {
        $now = time();
        $cms = AuthRule::query()->where('name', 'cms')->first();
        if (! $cms) {
            return;
        }

        foreach ([
            ['index' => 'cms/statistics/index', 'title' => '内容统计', 'icon' => 'fa fa-chart-pie', 'actions' => []],
            ['index' => 'cms/model-designer/index', 'title' => '模型设计', 'icon' => 'fa fa-cubes', 'actions' => ['add', 'edit', 'del', 'multi']],
            ['index' => 'cms/model-data/index', 'title' => '模型数据', 'icon' => 'fa fa-database', 'actions' => ['add', 'edit', 'del', 'multi', 'recyclebin', 'restore', 'destroy']],
        ] as $meta) {
            $menu = AuthRule::query()->firstOrCreate(
                ['name' => $meta['index']],
                [
                    'type' => 'file',
                    'pid' => $cms->id,
                    'title' => $meta['title'],
                    'icon' => $meta['icon'],
                    'ismenu' => 1,
                    'weigh' => 75,
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
        }

        Config::query()->updateOrCreate(
            ['name' => 'portal_theme'],
            [
                'group' => 'site',
                'title' => '门户主题',
                'type' => 'string',
                'value' => 'default',
                'visible' => '1',
                'content' => "default|默认\nclassic|经典",
            ]
        );

        $schema = json_encode([
            ['name' => 'title', 'label' => '名称', 'type' => 'text', 'list' => true, 'required' => true],
            ['name' => 'price', 'label' => '参考价', 'type' => 'number', 'list' => true],
            ['name' => 'desc', 'label' => '说明', 'type' => 'textarea'],
        ], JSON_UNESCAPED_UNICODE);

        $model = CmsModel::query()->firstOrCreate(
            ['name' => 'product'],
            [
                'title' => '产品推介',
                'fields' => $schema,
                'status' => 'normal',
                'weigh' => 100,
            ]
        );

        $sample = json_encode(['title' => '企业服务包', 'price' => '19999', 'desc' => '含实施与一年维保'], JSON_UNESCAPED_UNICODE);
        if (! CmsModelData::query()->where('model_id', $model->id)->where('data', $sample)->exists()) {
            CmsModelData::query()->create([
                'model_id' => $model->id,
                'data' => $sample,
                'status' => 'normal',
                'weigh' => 100,
                'createtime' => $now,
                'updatetime' => $now,
            ]);
        }

        $addonPath = config('cms.addons.path').'/cms-portal';
        if (is_dir($addonPath)) {
            try {
                app(AddonManager::class)->install('cms-portal');
            } catch (\Throwable) {
                //
            }
        }
    }
}
