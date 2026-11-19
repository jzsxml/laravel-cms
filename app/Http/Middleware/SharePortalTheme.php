<?php

namespace App\Http\Middleware;

use App\Support\PortalTheme;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SharePortalTheme
{
    public function handle(Request $request, Closure $next): Response
    {
        View::share('portalLayout', PortalTheme::layout());
        View::share('portalBodyClass', PortalTheme::bodyClass());

        return $next($request);
    }
}
