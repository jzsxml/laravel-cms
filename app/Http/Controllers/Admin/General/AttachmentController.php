<?php

namespace App\Http\Controllers\Admin\General;

use App\Http\Controllers\Admin\Controller;
use App\Models\Admin\Attachment;
use App\Support\AdminTableFilters;
use App\Support\AttachmentStorage;
use App\Support\BootstrapTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttachmentController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson() || $request->has('limit') || $request->has('offset')) {
            $table = app(BootstrapTable::class);
            $query = Attachment::query();
            $result = $table->fetch($query, $request, ['filename', 'url'], ['id', 'filename', 'url', 'filesize', 'mimetype', 'uploadtime']);

            return response()->json($result);
        }

        return view('admin.general.attachment.index', [
            'title' => '附件管理',
            'listFields' => ['id', 'filename', 'url', 'filesize', 'mimetype', 'uploadtime'],
            'urlPrefix' => 'general/attachment',
            'uploadUrl' => route('admin.ajax.upload'),
            'filterFields' => AdminTableFilters::fromListFields(['filename', 'mimetype', 'filesize', 'uploadtime']),
        ]);
    }

    public function del(Request $request): JsonResponse
    {
        $ids = $this->parseIds($request->input('ids', ''));
        if ($ids === []) {
            return response()->json(['code' => 0, 'msg' => '未选择记录']);
        }

        $rows = Attachment::query()->whereIn('id', $ids)->get();
        foreach ($rows as $row) {
            AttachmentStorage::deleteByUrl((string) $row->url);
        }
        Attachment::query()->whereIn('id', $ids)->delete();

        $this->adminAuth->recordLog($request, '附件', '删除: '.implode(',', $ids));

        return response()->json(['code' => 1, 'msg' => '删除成功']);
    }

    /**
     * @return array<int, int>
     */
    protected function parseIds(mixed $ids): array
    {
        if (is_array($ids)) {
            return array_values(array_filter(array_map('intval', $ids)));
        }

        return array_values(array_filter(array_map('intval', explode(',', (string) $ids))));
    }
}
