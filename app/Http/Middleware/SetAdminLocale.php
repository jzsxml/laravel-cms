<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetAdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = (array) config('cms.admin_locales', ['zh_CN', 'en']);
        $default = (string) config('cms.admin_locale', 'zh_CN');
        $locale = $default;

        if ($request->hasSession()) {
            $sessionKey = (string) config('cms.admin_locale_session', 'cms_admin_locale');
            $fromSession = (string) $request->session()->get($sessionKey, '');
            if ($fromSession !== '' && in_array($fromSession, $supported, true)) {
                $locale = $fromSession;
            }
        }

        $fromQuery = (string) $request->query('lang', '');
        if ($fromQuery !== '' && in_array($fromQuery, $supported, true)) {
            $locale = $fromQuery;
            if ($request->hasSession()) {
                $request->session()->put((string) config('cms.admin_locale_session', 'cms_admin_locale'), $locale);
            }
        }

        App::setLocale($locale);

        return $next($request);
    }
}
