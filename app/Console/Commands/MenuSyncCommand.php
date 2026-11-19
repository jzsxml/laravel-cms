<?php

namespace App\Console\Commands;

use App\Crud\MenuPublisher;
use App\Http\Controllers\Admin\CrudController;
use Illuminate\Console\Command;
use ReflectionClass;

class MenuSyncCommand extends Command
{
    protected $signature = 'cms:menu-sync {--dry : 仅预览不写入}';

    protected $description = '同步 CRUD 生成模块的菜单与权限规则';

    public function handle(MenuPublisher $publisher): int
    {
        $manifestPath = config('cms.crud.manifest');
        if (! is_file($manifestPath)) {
            $this->warn('未找到 generated_modules.php');

            return self::SUCCESS;
        }

        /** @var array<string, class-string> $modules */
        $modules = require $manifestPath;
        $dry = (bool) $this->option('dry');

        foreach ($modules as $slug => $class) {
            if (! class_exists($class)) {
                $this->warn("跳过 {$slug}：类不存在 {$class}");

                continue;
            }
            $controller = app($class);
            if (! $controller instanceof CrudController) {
                $this->warn("跳过 {$slug}：非 CrudController");

                continue;
            }

            $ref = new ReflectionClass($controller);
            $propTitle = $ref->getProperty('title');
            $propTitle->setAccessible(true);
            $propSoft = $ref->getProperty('softDelete');
            $propSoft->setAccessible(true);
            $title = $propTitle->getValue($controller) ?: $slug;
            $soft = (bool) $propSoft->getValue($controller);
            $actions = ['index', 'add', 'edit', 'del', 'multi', 'export'];
            if ($soft) {
                $actions = array_merge($actions, ['recyclebin', 'restore', 'destroy']);
            }

            $this->line("· {$slug}/index — {$title}".($soft ? ' [回收站]' : ''));
            if (! $dry) {
                $publisher->publish($slug, $title, $actions, $soft);
            }
        }

        $this->info($dry ? '预览完成' : '菜单规则已同步');

        return self::SUCCESS;
    }
}
