<?php

namespace App\Http\Controllers\Admin;

use App\Support\AdminTableFilters;
use App\Support\BootstrapTable;
use App\Support\TableExport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class CrudController extends Controller
{
    /** @var class-string<Model> */
    protected string $modelClass;

    protected string $title = 'CRUD';

    /** @var array<int, string> */
    protected array $listFields = ['id'];

    /** @var array<int, string> */
    protected array $searchFields = [];

    /** @var array<string, string> */
    protected array $formFields = [];

    protected bool $softDelete = false;

    protected string $softDeleteColumn = 'deletetime';

    /** 非超级管理员仅查看本人数据（需表含 dataScopeColumn） */
    protected bool $personalDataScope = false;

    protected string $dataScopeColumn = 'admin_id';

    /** 列表状态筛选（FastAdmin headingfilterfield） */
    protected ?string $headingFilterField = null;

    /** @var array<string, string> */
    protected array $headingFilterOptions = [
        '' => '全部',
        'normal' => '正常',
        'hidden' => '隐藏',
    ];

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson() || $request->has('limit')) {
            return $this->indexJson($request);
        }

        $headingField = $this->resolveHeadingFilterField();

        return view('admin.partials.crud-index', [
            'title' => $this->title,
            'listFields' => $this->listFields,
            'urlPrefix' => $this->urlSlug(),
            'headingFilterField' => $headingField,
            'headingFilterOptions' => $headingField ? $this->headingFilterOptions : [],
            'showRecyclebin' => $this->softDelete,
            'filterFields' => AdminTableFilters::fromListFields($this->listFields),
        ]);
    }

    protected function indexJson(Request $request): JsonResponse
    {
        $query = $this->baseQuery();
        if ($this->softDelete) {
            $query->whereNull($this->softDeleteColumn);
        }

        $table = app(BootstrapTable::class);
        $result = $table->fetch($query, $request, $this->searchFields, $this->listFields);
        $result['rows'] = $this->transformRows($result['rows']);

        return response()->json($result);
    }

    public function export(Request $request)
    {
        $query = $this->baseQuery();
        if ($this->softDelete) {
            $query->whereNull($this->softDeleteColumn);
        }

        $exportRequest = clone $request;
        $exportRequest->merge(['offset' => 0, 'limit' => 10000]);

        $table = app(BootstrapTable::class);
        $result = $table->fetch($query, $exportRequest, $this->searchFields, $this->listFields);
        $rows = $this->transformRows($result['rows']);

        $this->adminAuth->recordLog($request, $this->title, '导出 Excel');

        return TableExport::xlsxResponse($rows, $this->listFields, $this->title);
    }

    public function add(Request $request): View|JsonResponse
    {
        if ($request->isMethod('post')) {
            $data = $this->validateSave($request);
            $model = $this->newModel();
            $model->fill($data);
            $model->save();
            $this->adminAuth->recordLog($request, $this->title, '新增');
            $this->afterMutation($request, 'add');

            return response()->json(['code' => 1, 'msg' => '添加成功']);
        }

        return view('admin.partials.crud-form', [
            'title' => $this->title,
            'record' => $this->newModel(),
            'formFields' => $this->formFields,
            'formExtras' => $this->formExtras(),
            'action' => url(config('cms.admin_path').'/'.$this->urlSlug().'/add'),
            'backUrl' => url(config('cms.admin_path').'/'.$this->urlSlug()),
        ]);
    }

    public function edit(Request $request): View|JsonResponse
    {
        $ids = $request->input('ids', $request->route('ids'));
        $model = $this->findModel((int) $ids);
        if (! $model) {
            return response()->json(['code' => 0, 'msg' => '记录不存在'], 404);
        }

        if ($request->isMethod('post')) {
            $data = $this->validateSave($request, $model);
            $model->fill($data);
            $model->save();
            $this->adminAuth->recordLog($request, $this->title, '编辑 ID:'.$model->getKey());
            $this->afterMutation($request, 'edit');

            return response()->json(['code' => 1, 'msg' => '保存成功']);
        }

        return view('admin.partials.crud-form', [
            'title' => $this->title,
            'record' => $model,
            'formFields' => $this->formFields,
            'formExtras' => $this->formExtras(),
            'action' => url(config('cms.admin_path').'/'.$this->urlSlug().'/edit?ids='.$model->getKey()),
            'backUrl' => url(config('cms.admin_path').'/'.$this->urlSlug()),
        ]);
    }

    public function del(Request $request): JsonResponse
    {
        $ids = $this->parseIds($request->input('ids', ''));
        if ($ids === []) {
            return response()->json(['code' => 0, 'msg' => '未选择记录']);
        }

        $query = $this->modelClass::query()->whereIn('id', $ids);
        if ($this->softDelete) {
            $query->update([$this->softDeleteColumn => time()]);
        } else {
            $query->delete();
        }

        $this->adminAuth->recordLog($request, $this->title, '删除: '.implode(',', $ids));
        $this->afterMutation($request, 'del');

        return response()->json(['code' => 1, 'msg' => '删除成功']);
    }

    public function multi(Request $request): JsonResponse
    {
        $ids = $this->parseIds($request->input('ids', ''));
        $field = (string) $request->input('params', '');
        $value = $request->input('value');

        if ($ids === [] || $field === '' || ! in_array($field, $this->listFields, true)) {
            return response()->json(['code' => 0, 'msg' => '参数错误']);
        }

        $this->modelClass::query()->whereIn('id', $ids)->update([$field => $value]);
        $this->afterMutation($request, 'multi');

        return response()->json(['code' => 1, 'msg' => '批量更新成功']);
    }

    public function recyclebin(Request $request): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson() || $request->has('limit') || $request->has('offset')) {
            $query = $this->baseQuery()->whereNotNull($this->softDeleteColumn);
            $table = app(BootstrapTable::class);
            $result = $table->fetch($query, $request, [], $this->listFields);
            $result['rows'] = $this->transformRows($result['rows']);

            return response()->json($result);
        }

        return view('admin.partials.crud-recyclebin', [
            'title' => $this->title.' — 回收站',
            'listFields' => $this->listFields,
            'urlPrefix' => $this->urlSlug(),
            'filterFields' => AdminTableFilters::fromListFields($this->listFields),
        ]);
    }

    public function restore(Request $request): JsonResponse
    {
        $ids = $this->parseIds($request->input('ids', ''));
        if ($ids === []) {
            return response()->json(['code' => 0, 'msg' => '未选择记录']);
        }
        $this->modelClass::query()->whereIn('id', $ids)->update([$this->softDeleteColumn => null]);
        $this->adminAuth->recordLog($request, $this->title, '还原: '.implode(',', $ids));

        return response()->json(['code' => 1, 'msg' => '还原成功']);
    }

    public function destroy(Request $request): JsonResponse
    {
        $ids = $this->parseIds($request->input('ids', ''));
        if ($ids === []) {
            return response()->json(['code' => 0, 'msg' => '未选择记录']);
        }
        $this->modelClass::query()->whereIn('id', $ids)->delete();
        $this->adminAuth->recordLog($request, $this->title, '销毁: '.implode(',', $ids));

        return response()->json(['code' => 1, 'msg' => '销毁成功']);
    }

    protected function baseQuery(): Builder
    {
        $query = $this->modelClass::query();
        $this->applyPersonalDataScope($query);

        return $query;
    }

    protected function applyPersonalDataScope(Builder $query): void
    {
        if (! $this->personalDataScope) {
            return;
        }
        if (in_array('*', $this->adminAuth->ruleIds(), true)) {
            return;
        }
        $adminId = $this->adminAuth->adminId();
        if ($adminId) {
            $query->where($this->dataScopeColumn, $adminId);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function formExtras(): array
    {
        return [];
    }

    protected function resolveHeadingFilterField(): ?string
    {
        if ($this->headingFilterField !== null) {
            return $this->headingFilterField;
        }
        if (in_array('status', $this->listFields, true)) {
            return 'status';
        }

        return null;
    }

    /**
     * @param  array<int, mixed>  $rows
     * @return array<int, mixed>
     */
    protected function transformRows(array $rows): array
    {
        return array_map(function ($row) {
            if ($row instanceof Model) {
                return $row->toArray();
            }

            return $row;
        }, $rows);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateSave(Request $request, ?Model $model = null): array
    {
        $rules = [];
        foreach (array_keys($this->formFields) as $field) {
            $rules[$field] = in_array($field, ['title'], true) ? 'required|string|max:255' : 'nullable';
        }

        return $request->validate($rules);
    }

    protected function newModel(): Model
    {
        return new $this->modelClass;
    }

    protected function findModel(int $id): ?Model
    {
        $query = $this->modelClass::query()->whereKey($id);
        if ($this->softDelete) {
            $query->whereNull($this->softDeleteColumn);
        }

        return $query->first();
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

    protected function viewName(string $suffix): string
    {
        return 'admin.crud.'.$this->urlSlug().'.'.$suffix;
    }

    protected function urlSlug(): string
    {
        $class = static::class;
        $relative = str_replace('App\\Http\\Controllers\\Admin\\', '', $class);
        $relative = str_replace('\\', '/', $relative);
        $relative = preg_replace('/Controller$/', '', $relative) ?? $relative;

        return strtolower($relative);
    }

    protected function afterMutation(Request $request, string $action): void
    {
        // Subclasses may invalidate caches after writes.
    }
}
