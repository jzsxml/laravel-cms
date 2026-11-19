<?php

namespace App\Http\Controllers\Admin\General;

use App\Http\Controllers\Admin\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class DevCommandController extends Controller
{
    public function index(): View
    {
        return view('admin.general.command.index', [
            'whitelist' => config('cms.commands_whitelist', []),
        ]);
    }

    public function run(Request $request): JsonResponse
    {
        $command = trim((string) $request->input('command', ''));
        $whitelist = config('cms.commands_whitelist', []);
        if ($command === '' || ! in_array($command, $whitelist, true)) {
            return response()->json(['code' => 0, 'msg' => '命令不在白名单'], 403);
        }

        $exit = match ($command) {
            'migrate:status' => Artisan::call('migrate:status'),
            'route:list' => Artisan::call('route:list'),
            'addon list' => Artisan::call('addon', ['action' => 'list']),
            'cms:menu-sync' => Artisan::call('cms:menu-sync'),
            default => 1,
        };

        $output = Artisan::output();
        $this->adminAuth->recordLog($request, '在线命令', $command);

        return response()->json([
            'code' => $exit === 0 ? 1 : 0,
            'msg' => $exit === 0 ? '执行完成' : '执行失败',
            'data' => ['output' => $output, 'exit' => $exit],
        ]);
    }
}
