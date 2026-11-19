<?php

namespace App\Http\Controllers\Admin\General;

use App\Http\Controllers\Admin\Controller;
use App\Services\Admin\AdminAuth;
use App\Services\Admin\AdminCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CacheController extends Controller
{
    public function __construct(
        AdminAuth $adminAuth,
        protected AdminCache $cache
    ) {
        parent::__construct($adminAuth);
    }

    public function index(): View
    {
        return view('admin.general.cache.index', [
            'catalog' => $this->cache->catalog(),
            'meta' => $this->cache->meta(),
            'ttl' => $this->cache->ttl(),
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $meta = $this->cache->refresh();
        $this->adminAuth->recordLog($request, '更新缓存', '手动刷新后台数据缓存');

        return response()->json([
            'code' => 1,
            'msg' => '缓存已更新',
            'data' => $meta,
        ]);
    }
}
