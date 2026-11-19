<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminPermission
{
    public static function routeName(Request $request): ?string
    {
        $route = $request->route();
        if (! $route) {
            return null;
        }

        $controller = $route->getControllerClass();
        if (! $controller || ! Str::startsWith($controller, 'App\\Http\\Controllers\\Admin\\')) {
            return null;
        }

        $relative = Str::after($controller, 'App\\Http\\Controllers\\Admin\\');
        $relative = Str::replace('\\', '/', $relative);
        $relative = preg_replace('/Controller$/', '', $relative) ?? $relative;

        return strtolower($relative.'/'.$route->getActionMethod());
    }
}
