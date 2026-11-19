<?php

namespace App\Providers;

use App\Addon\AddonManager;
use App\Services\Admin\AdminAuth;
use App\Services\Admin\AdminCache;
use App\Support\AdminLang;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AdminAuth::class);
        $this->app->singleton(AdminCache::class);
        $this->app->singleton(AddonManager::class);

        $this->app->booting(function () {
            app(AddonManager::class)->registerEnabledProviders();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('cms-login', function (Request $request) {
            $max = max(1, (int) config('cms.security.login.max_attempts', 5));
            $decay = max(1, (int) config('cms.security.login.decay_minutes', 1));
            $identity = strtolower((string) (
                $request->input('username')
                ?? $request->input('account')
                ?? $request->input('mobile')
                ?? ''
            ));

            return Limit::perMinutes($decay, $max)->by($request->ip().'|'.$identity);
        });

        View::composer('admin.*', function ($view) {
            $data = $view->getData();
            if (isset($data['listFields']) && ! isset($data['listFieldLabels'])) {
                $view->with('listFieldLabels', AdminLang::labelsFor($data['listFields']));
            }
        });

        View::composer('admin.layouts.app', function ($view) {
            $auth = app(AdminAuth::class);
            if ($auth->user()) {
                $view->with('admin', $auth->user());
                $view->with('menu', $auth->menuTree());
            }
        });
    }
}
