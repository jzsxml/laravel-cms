<?php

namespace App\Http\Controllers\Admin\General;

use App\Http\Controllers\Admin\Controller;
use App\Models\Admin\AdminLog;
use App\Support\AdminTableFilters;
use App\Support\BootstrapTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminLogController extends Controller
{
    /** @var array<int, string> */
    protected array $listFields = ['id', 'username', 'title', 'ip', 'url', 'createtime'];

    /** @var array<int, string> */
    protected array $searchFields = ['username', 'title', 'url', 'ip', 'content'];

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson() || $request->has('limit') || $request->has('offset')) {
            $table = app(BootstrapTable::class);
            $query = AdminLog::query()->orderByDesc('id');
            $result = $table->fetch($query, $request, $this->searchFields, $this->listFields);

            $result['rows'] = array_map(function ($row) {
                $arr = $row instanceof AdminLog ? $row->toArray() : (array) $row;
                if (! empty($arr['createtime'])) {
                    $arr['createtime'] = date('Y-m-d H:i:s', (int) $arr['createtime']);
                }
                if (! empty($arr['url']) && mb_strlen((string) $arr['url']) > 60) {
                    $arr['url'] = mb_substr((string) $arr['url'], 0, 60).'…';
                }

                return $arr;
            }, $result['rows']);

            return response()->json($result);
        }

        $filterFields = AdminTableFilters::fromListFields([
            'username',
            'title',
            'ip',
            'admin_id',
            'createtime',
        ]);

        $filterFields['title']['label'] = '标题 / 操作';
        $filterFields['title']['defaultOp'] = 'LIKE';

        return view('admin.general.adminlog.index', [
            'title' => '管理员日志',
            'listFields' => $this->listFields,
            'urlPrefix' => 'general/adminlog',
            'filterFields' => $filterFields,
        ]);
    }
}
