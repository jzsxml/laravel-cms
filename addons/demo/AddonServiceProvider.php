<?php

namespace Addons\Demo;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AddonServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $admin = config('cms.admin_path', 'admin');
        Route::middleware(['web', 'admin.auth', 'admin.permission'])
            ->prefix($admin.'/addon/demo')
            ->group(__DIR__.'/routes.php');

        if (file_exists(__DIR__.'/routes-api.php')) {
            Route::middleware('api')->prefix('api/addon/demo')->group(__DIR__.'/routes-api.php');
        }
    }
}