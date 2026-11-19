<?php

namespace App\Crud;

use App\Models\Admin\AuthRule;
use App\Services\Admin\AdminCache;
use Illuminate\Support\Str;

class MenuPublisher
{
    /**
     * @param  array<int, string>  $actions
     */
    public function publish(string $slug, string $title, array $actions, bool $recyclebin, bool $delete = false): void
    {
        if ($delete) {
            AuthRule::query()->where(function ($query) use ($slug) {
                $query->where('name', 'like', $slug.'/%')
                    ->orWhere('name', $slug.'/index');
            })->delete();
            app(AdminCache::class)->forgetAuthRules();

            return;
        }

        $now = time();
        $main = AuthRule::query()->firstOrCreate(
            ['name' => $slug.'/index'],
            [
                'type' => 'file',
                'pid' => 0,
                'title' => $title,
                'icon' => 'fa fa-circle-o',
                'ismenu' => 1,
                'weigh' => 0,
                'status' => 'normal',
                'createtime' => $now,
                'updatetime' => $now,
            ]
        );
        $main->update(['title' => $title, 'ismenu' => 1, 'updatetime' => $now]);

        $py = $this->pinyinAbbr($title);
        $main->update(['py' => $py, 'pinyin' => Str::lower($title)]);

        foreach ($actions as $action) {
            if ($action === 'index') {
                continue;
            }
            AuthRule::query()->firstOrCreate(
                ['name' => $slug.'/'.$action],
                [
                    'type' => 'file',
                    'pid' => $main->id,
                    'title' => $this->actionTitle($action),
                    'icon' => '',
                    'ismenu' => 0,
                    'weigh' => 0,
                    'status' => 'normal',
                    'createtime' => $now,
                    'updatetime' => $now,
                ]
            );
        }

        app(AdminCache::class)->forgetAuthRules();
    }

    protected function actionTitle(string $action): string
    {
        return match ($action) {
            'add' => '添加',
            'edit' => '编辑',
            'del' => '删除',
            'multi' => '批量',
            'recyclebin' => '回收站',
            'restore' => '还原',
            'destroy' => '销毁',
            'export' => '导出',
            default => $action,
        };
    }

    protected function pinyinAbbr(string $title): string
    {
        return Str::lower(Str::substr(preg_replace('/\s+/', '', $title) ?? $title, 0, 8));
    }
}
