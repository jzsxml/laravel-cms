<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Admin\CrudController;
use App\Models\Admin\CmsModel;
use App\Models\Admin\CmsModelData;
use App\Services\Cms\ModelSchema;
use App\Support\BootstrapTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModelDataController extends CrudController
{
    protected string $modelClass = CmsModelData::class;

    protected string $title = '模型数据';

    protected array $listFields = ['id', 'list_title', 'status', 'weigh', 'createtime'];

    protected array $searchFields = [];

    protected bool $softDelete = true;

    protected string $softDeleteColumn = 'deletetime';

    protected ?CmsModel $cmsModel = null;

    protected function resolveModel(Request $request): CmsModel
    {
        if ($this->cmsModel) {
            return $this->cmsModel;
        }
        $modelId = (int) $request->input('model_id', $request->query('model_id', 0));
        $this->cmsModel = CmsModel::query()->findOrFail($modelId);

        return $this->cmsModel;
    }

    protected function baseQuery(): Builder
    {
        $request = request();
        $cmsModel = $this->resolveModel($request);

        return parent::baseQuery()->where('model_id', $cmsModel->id);
    }

    public function index(Request $request): View|JsonResponse
    {
        $modelId = (int) $request->input('model_id', $request->query('model_id', 0));
        if ($modelId === 0 && ! $request->ajax() && ! $request->wantsJson() && ! $request->has('limit')) {
            return view('admin.cms.model-data-picker', [
                'models' => CmsModel::query()->where('status', 'normal')->orderByDesc('weigh')->get(),
            ]);
        }

        $cmsModel = $this->resolveModel($request);
        $this->title = $cmsModel->title.' — 数据';

        if ($request->ajax() || $request->wantsJson() || $request->has('limit')) {
            $query = $this->baseQuery()->whereNull($this->softDeleteColumn);
            $table = app(BootstrapTable::class);
            $result = $table->fetch($query, $request, []);
            $schema = app(ModelSchema::class);
            $rows = [];
            foreach ($result['rows'] as $row) {
                if ($row instanceof CmsModelData) {
                    $arr = $row->toArray();
                    $arr['list_title'] = $schema->listTitle($cmsModel, $row);
                    $rows[] = $arr;
                }
            }
            $result['rows'] = $rows;

            return response()->json($result);
        }

        return view('admin.partials.crud-index', [
            'title' => $this->title,
            'listFields' => $this->listFields,
            'urlPrefix' => $this->urlSlug(),
            'headingFilterField' => null,
            'headingFilterOptions' => [],
            'showRecyclebin' => true,
            'filterFields' => [],
            'extraQuery' => ['model_id' => $cmsModel->id],
        ]);
    }

    public function add(Request $request): View|JsonResponse
    {
        $cmsModel = $this->resolveModel($request);
        if ($request->isMethod('post')) {
            $payload = app(ModelSchema::class)->validateData($cmsModel, $request);
            $row = CmsModelData::query()->create([
                'model_id' => $cmsModel->id,
                'data' => app(ModelSchema::class)->encodeData($payload),
                'status' => $request->input('status', 'normal'),
                'weigh' => (int) $request->input('weigh', 0),
            ]);
            $this->adminAuth->recordLog($request, $this->title, '新增 #'.$row->getKey());

            return response()->json(['code' => 1, 'msg' => '添加成功']);
        }

        return view('admin.cms.model-data-form', [
            'title' => '添加 — '.$cmsModel->title,
            'cmsModel' => $cmsModel,
            'record' => new CmsModelData,
            'action' => url(config('cms.admin_path').'/'.$this->urlSlug().'/add?model_id='.$cmsModel->id),
            'backUrl' => url(config('cms.admin_path').'/'.$this->urlSlug().'?model_id='.$cmsModel->id),
        ]);
    }

    public function edit(Request $request): View|JsonResponse
    {
        $cmsModel = $this->resolveModel($request);
        $ids = (int) $request->input('ids', $request->query('ids'));
        $record = CmsModelData::query()->where('model_id', $cmsModel->id)->findOrFail($ids);

        if ($request->isMethod('post')) {
            $payload = app(ModelSchema::class)->validateData($cmsModel, $request);
            $record->data = app(ModelSchema::class)->encodeData($payload);
            $record->status = $request->input('status', 'normal');
            $record->weigh = (int) $request->input('weigh', 0);
            $record->save();
            $this->adminAuth->recordLog($request, $this->title, '编辑 #'.$record->getKey());

            return response()->json(['code' => 1, 'msg' => '保存成功']);
        }

        foreach ($record->dataArray() as $k => $v) {
            $record->setAttribute($k, $v);
        }

        return view('admin.cms.model-data-form', [
            'title' => '编辑 — '.$cmsModel->title,
            'cmsModel' => $cmsModel,
            'record' => $record,
            'action' => url(config('cms.admin_path').'/'.$this->urlSlug().'/edit?model_id='.$cmsModel->id.'&ids='.$record->getKey()),
            'backUrl' => url(config('cms.admin_path').'/'.$this->urlSlug().'?model_id='.$cmsModel->id),
        ]);
    }

    protected function validateSave(Request $request, ?Model $model = null): array
    {
        return [];
    }

    protected function findModel(int $id): ?Model
    {
        $modelId = (int) request()->input('model_id', request()->query('model_id', 0));

        return CmsModelData::query()->where('model_id', $modelId)->find($id);
    }
}
