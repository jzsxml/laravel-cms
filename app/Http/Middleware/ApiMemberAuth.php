<?php

namespace App\Http\Middleware;

use App\Models\Api\Member;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiMemberAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Cms-Token')
            ?: $request->header('Token')
            ?: $request->input('token');

        if (! $token) {
            return response()->json(['code' => 401, 'msg' => '请先登录', 'data' => null], 401);
        }

        $member = Member::query()->where('token', $token)->where('status', 'normal')->first();
        if (! $member) {
            return response()->json(['code' => 401, 'msg' => 'Token 无效', 'data' => null], 401);
        }

        $request->attributes->set('member', $member);

        return $next($request);
    }
}
