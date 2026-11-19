<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\Admin;
use App\Models\Admin\AdminLog;
use App\Models\Admin\Attachment;
use App\Models\Admin\Category;
use App\Models\Admin\Test;
use App\Models\Api\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndexController extends Controller
{
    public function index(): View
    {
        $todayStart = strtotime('today');
        $sevenDaysAgo = strtotime('-6 days', $todayStart);

        $loginTrend = [];
        for ($d = $sevenDaysAgo; $d <= $todayStart; $d += 86400) {
            $next = $d + 86400;
            $loginTrend[] = [
                'label' => date('m-d', $d),
                'count' => AdminLog::query()
                    ->where('title', '登录')
                    ->whereBetween('createtime', [$d, $next - 1])
                    ->count(),
            ];
        }
        $maxLogin = max(1, ...array_column($loginTrend, 'count'));

        return view('admin.index.dashboard', [
            'admin' => $this->adminAuth->user(),
            'menu' => $this->adminAuth->menuTree(),
            'stats' => [
                'admins' => Admin::query()->where('status', 'normal')->count(),
                'members' => Member::query()->where('status', 'normal')->count(),
                'attachments' => Attachment::query()->count(),
                'categories' => Category::query()->where('status', 'normal')->count(),
                'tests' => Test::query()->whereNull('deletetime')->count(),
                'logins_today' => AdminLog::query()->where('title', '登录')->where('createtime', '>=', $todayStart)->count(),
                'logs_today' => AdminLog::query()->where('createtime', '>=', $todayStart)->count(),
            ],
            'loginTrend' => $loginTrend,
            'maxLogin' => $maxLogin,
            'recentLogs' => AdminLog::query()->orderByDesc('id')->limit(10)->get(),
        ]);
    }

    public function menu(): JsonResponse
    {
        return response()->json([
            'code' => 1,
            'msg' => 'ok',
            'data' => $this->adminAuth->menuTree(),
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->adminAuth->recordLog($request, '退出', '管理员退出登录');
        $this->adminAuth->logout();

        return redirect()->route('admin.login');
    }
}
