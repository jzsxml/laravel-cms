<?php

namespace App\Http\Middleware;

use App\Services\Admin\AdminAuth;
use App\Support\AdminPermission as AdminPermissionSupport;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminPermission
{
    /** @var array<int, string> */
    protected array $except = [
        'index/index',
        'index/logout',
        'ajax/selectpage',
        'general/profile/index',
    ];

    public function __construct(protected AdminAuth $auth) {}

    public function handle(Request $request, Closure $next): Response
    {
        $name = AdminPermissionSupport::routeName($request);
        if (! $name || in_array($name, $this->except, true)) {
            return $next($request);
        }

        if (! $this->auth->check($name)) {
            if ($request->expectsJson()) {
                return response()->json(['code' => 0, 'msg' => '没有权限', 'data' => null], 403);
            }

            abort(403, '没有权限');
        }

        return $next($request);
    }
}
