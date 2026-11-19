<?php

namespace App\Http\Middleware;

use App\Services\Member\MemberAuth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MemberAuthenticate
{
    public function __construct(protected MemberAuth $memberAuth) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->memberAuth->check()) {
            $path = config('cms.member_path', 'user');

            return redirect()->guest(url($path.'/login'));
        }

        return $next($request);
    }
}
