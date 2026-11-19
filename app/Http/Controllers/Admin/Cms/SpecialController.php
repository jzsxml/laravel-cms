<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Admin\CrudController;
use App\Models\Admin\Archive;
use App\Models\Admin\Special;
use App\Support\ArchiveSlug;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SpecialController extends CrudController
{
    protected string $modelClass = Special::class;

    protected string $title = '专题管理';

    protected array $listFields = ['id', 'title', 'slug', 'weigh', 'status', 'updatetime'];

    protected array $searchFields = ['title', 'slug'];

    protected array $formFields = [
        'title' => 'text:标题',
        'slug' => 'text:Slug',
        'image' => 'image:封面',
        'description' => 'textarea:摘要',
        'content' => 'editor:导语',
        'weigh' => 'number:权重',
        'status' => 'select:状态',
        'archive_ids' => 'textarea:关联文章ID',
    ];

    protected bool $softDelete = true;

    protected string $softDeleteColumn = 'deletetime';

    protected function formExtras(): array
    {
        return [
            'statusOptions' => ['normal' => '正常', 'hidden' => '隐藏'],
            'slugHint' => '前台 /s/{slug}',
            'archiveIdsHint' => '逗号分隔文章 ID，保存时写入专题关联',
        ];
    }

    public function edit(Request $request): View|JsonResponse
    {
        $ids = $request->input('ids', $request->route('ids'));
        $model = $this->findModel((int) $ids);
        if (! $model) {
            return response()->json(['code' => 0, 'msg' => '记录不存在'], 404);
        }

        if ($request->isMethod('post')) {
            $archiveIds = (string) $request->input('archive_ids', '');
            $data = $this->validateSave($request, $model);
            $model->fill($data);
            $model->save();
            $this->syncArchives($model, $archiveIds);
            $this->adminAuth->recordLog($request, $this->title, '编辑 ID:'.$model->getKey());

            return response()->json(['code' => 1, 'msg' => '保存成功']);
        }

        $model->setAttribute('archive_ids', $model->archives()->pluck('id')->implode(','));

        return view('admin.partials.crud-form', [
            'title' => $this->title,
            'record' => $model,
            'formFields' => $this->formFields,
            'formExtras' => $this->formExtras(),
            'action' => url(config('cms.admin_path').'/'.$this->urlSlug().'/edit?ids='.$model->getKey()),
            'backUrl' => url(config('cms.admin_path').'/'.$this->urlSlug()),
        ]);
    }

    public function add(Request $request): View|JsonResponse
    {
        if ($request->isMethod('post')) {
            $archiveIds = (string) $request->input('archive_ids', '');
            $data = $this->validateSave($request);
            $model = $this->newModel();
            $model->fill($data);
            $model->save();
            $this->syncArchives($model, $archiveIds);
            $this->adminAuth->recordLog($request, $this->title, '新增');

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

    public function batchArchives(Request $request): JsonResponse
    {
        $ids = $this->parseIds($request->input('ids', ''));
        $specialId = (int) $request->input('special_id', 0);
        $special = Special::query()->find($specialId);
        if ($ids === [] || ! $special) {
            return response()->json(['code' => 0, 'msg' => '参数错误']);
        }

        $special->archives()->syncWithoutDetaching(
            Archive::query()->whereIn('id', $ids)->pluck('id')->all()
        );

        return response()->json(['code' => 1, 'msg' => '已加入专题']);
    }

    protected function syncArchives(Special $special, string $raw): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', preg_split('/[,，\s]+/', $raw) ?: []))));
        $valid = Archive::query()->whereIn('id', $ids)->pluck('id')->all();
        $special->archives()->sync($valid);
    }

    protected function validateSave(Request $request, ?Model $model = null): array
    {
        $id = $model?->getKey();
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:120', Rule::unique(Special::class, 'slug')->ignore($id)],
            'image' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'weigh' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:normal,hidden'],
        ]);

        $slug = trim((string) ($data['slug'] ?? ''));
        if ($slug === '') {
            $slug = ArchiveSlug::slugify($data['title']);
        } else {
            $slug = ArchiveSlug::slugify($slug);
        }
        if ($slug === '' || ! ArchiveSlug::validate($slug)) {
            throw ValidationException::withMessages(['slug' => 'Slug 无效']);
        }
        $base = $slug;
        $n = 1;
        while (Special::query()->when($id, fn ($q) => $q->where('id', '!=', $id))->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$n++;
        }
        $data['slug'] = $slug;
        $data['weigh'] = (int) ($data['weigh'] ?? 0);

        if (array_key_exists('content', $data)) {
            $data['content'] = HtmlSanitizer::clean($data['content']);
        }

        return $data;
    }
}
