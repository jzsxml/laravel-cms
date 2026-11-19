<?php

namespace App\Http\Middleware;

use App\Services\Admin\AdminAuth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    public function __construct(protected AdminAuth $auth) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->auth->user()) {
            if ($request->expectsJson()) {
                return response()->json(['code' => 0, 'msg' => '请先登录', 'data' => null], 401);
            }

            return redirect()->guest(route('admin.login'));
        }

        $admin = $this->auth->adminModel();
        if (! $admin || $admin->status !== 'normal') {
            $this->auth->logout();

            return redirect()->route('admin.login')->with('error', '账号已禁用');
        }

        return $next($request);
    }
}
