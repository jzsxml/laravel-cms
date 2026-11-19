<?php

namespace App\Http\Controllers\Admin\General;

use App\Addon\AddonManager;
use App\Http\Controllers\Admin\Controller;
use App\Services\Admin\AdminAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AddonController extends Controller
{
    public function __construct(
        AdminAuth $adminAuth,
        protected AddonManager $addons
    ) {
        parent::__construct($adminAuth);
    }

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'total' => count($this->addons->all()),
                'rows' => $this->addons->all(),
            ]);
        }

        return view('admin.general.addon.index');
    }

    public function install(Request $request): JsonResponse
    {
        $name = strtolower((string) $request->input('name'));

        try {
            $this->addons->install($name);
        } catch (\Throwable $e) {
            return response()->json(['code' => 0, 'msg' => $e->getMessage()]);
        }
        $this->adminAuth->recordLog($request, '插件', '安装 '.$name);

        return response()->json(['code' => 1, 'msg' => '安装并启用成功，请刷新整个后台页面以加载插件路由']);
    }

    public function uninstall(Request $request): JsonResponse
    {
        $name = strtolower((string) $request->input('name'));

        try {
            $this->addons->uninstall($name);
        } catch (\Throwable $e) {
            return response()->json(['code' => 0, 'msg' => $e->getMessage()]);
        }
        $this->adminAuth->recordLog($request, '插件', '卸载 '.$name);

        return response()->json(['code' => 1, 'msg' => '已卸载（文件仍保留在 addons/ 目录），请刷新整个后台页面']);
    }

    public function enable(Request $request): JsonResponse
    {
        $name = strtolower((string) $request->input('name'));
        if ($name === '') {
            return response()->json(['code' => 0, 'msg' => '缺少插件标识']);
        }

        try {
            $this->addons->enable($name);
        } catch (\Throwable $e) {
            return response()->json(['code' => 0, 'msg' => $e->getMessage()]);
        }
        $this->adminAuth->recordLog($request, '插件', '启用 '.$name);

        return response()->json(['code' => 1, 'msg' => '已启用，请刷新整个后台页面以加载插件路由']);
    }

    public function disable(Request $request): JsonResponse
    {
        $name = strtolower((string) $request->input('name'));
        if ($name === '') {
            return response()->json(['code' => 0, 'msg' => '缺少插件标识']);
        }

        try {
            $this->addons->disable($name);
        } catch (\Throwable $e) {
            return response()->json(['code' => 0, 'msg' => $e->getMessage()]);
        }
        $this->adminAuth->recordLog($request, '插件', '禁用 '.$name);

        return response()->json(['code' => 1, 'msg' => '已禁用，请刷新整个后台页面']);
    }
}
