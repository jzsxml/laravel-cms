<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Admin\CrudController;
use App\Models\Admin\Archive;
use App\Models\Admin\Special;
use App\Services\Cms\ArchiveRelations;
use App\Support\AdminTableFilters;
use App\Support\ArchiveSlug;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ArchivesController extends CrudController
{
    protected string $modelClass = Archive::class;

    protected string $title = '文章管理';

    protected array $listFields = ['id', 'category_id', 'title', 'slug', 'pay_type', 'price', 'status', 'flag', 'weigh', 'publishtime'];

    protected array $searchFields = ['title', 'slug', 'description'];

    protected array $formFields = [
        'category_id' => 'selectpage:主栏目',
        'extra_category_ids' => 'textarea:副栏目ID',
        'title' => 'text:标题',
        'slug' => 'text:URL Slug',
        'subtitle' => 'text:副标题',
        'tag_names' => 'textarea:标签',
        'image' => 'image:封面',
        'description' => 'textarea:摘要',
        'keywords' => 'text:SEO关键字',
        'flag' => 'text:标志',
        'pay_type' => 'select:付费类型',
        'price' => 'number:价格(元)',
        'pay_ratio' => 'number:试读比例%',
        'download_url' => 'text:附件URL',
        'weigh' => 'number:权重',
        'status' => 'select:状态',
        'content' => 'editor:正文',
    ];

    protected bool $softDelete = true;

    protected string $softDeleteColumn = 'deletetime';

    protected ?string $headingFilterField = 'status';

    /** @var array<string, string> */
    protected array $headingFilterOptions = [
        '' => '全部',
        'draft' => '草稿',
        'normal' => '已发布',
        'hidden' => '已下线',
        'pending' => '待审核',
        'rejected' => '已拒稿',
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
            'archiveBatch' => true,
            'specials' => Special::query()->where('status', 'normal')->whereNull('deletetime')->orderByDesc('weigh')->get(),
        ]);
    }

    public function approve(Request $request): JsonResponse
    {
        $ids = $this->parseIds($request->input('ids', ''));
        if ($ids === []) {
            return response()->json(['code' => 0, 'msg' => '未选择记录']);
        }
        Archive::query()
            ->whereIn('id', $ids)
            ->where('status', 'pending')
            ->update(['status' => 'normal', 'publishtime' => time(), 'updatetime' => time()]);
        $this->adminAuth->recordLog($request, $this->title, '审核通过: '.implode(',', $ids));

        return response()->json(['code' => 1, 'msg' => '已通过并发布']);
    }

    public function reject(Request $request): JsonResponse
    {
        $ids = $this->parseIds($request->input('ids', ''));
        if ($ids === []) {
            return response()->json(['code' => 0, 'msg' => '未选择记录']);
        }
        Archive::query()->whereIn('id', $ids)->where('status', 'pending')->update([
            'status' => 'rejected',
            'updatetime' => time(),
        ]);

        return response()->json(['code' => 1, 'msg' => '已拒稿']);
    }

    public function batchSpecial(Request $request): JsonResponse
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

    public function batchTags(Request $request): JsonResponse
    {
        $ids = $this->parseIds($request->input('ids', ''));
        $tagNames = (string) $request->input('tag_names', '');
        if ($ids === [] || $tagNames === '') {
            return response()->json(['code' => 0, 'msg' => '请选择文章并填写标签']);
        }
        $relations = app(ArchiveRelations::class);
        foreach (Archive::query()->whereIn('id', $ids)->get() as $archive) {
            $existing = $relations->tagNamesForForm($archive);
            $merged = trim($existing.','.$tagNames, ',');
            $relations->syncTags($archive, $merged);
        }

        return response()->json(['code' => 1, 'msg' => '标签已追加']);
    }

    protected function baseQuery(): Builder
    {
        return parent::baseQuery()->with(['category', 'tags']);
    }

    protected function formExtras(): array
    {
        return [
            'statusOptions' => [
                'draft' => '草稿',
                'pending' => '待审核',
                'normal' => '已发布',
                'hidden' => '已下线',
                'rejected' => '已拒稿',
            ],
            'categorySelectType' => 'article',
            'slugHint' => '留空将根据标题自动生成拼音；修改后已分享链接将变化。',
            'extraCategoryHint' => '逗号分隔栏目 ID，不可与主栏目相同；副栏目列表页也会展示该文。',
            'tagHint' => '逗号分隔，保存时自动创建标签',
            'flagHint' => '如 top,recommend',
            'payTypeOptions' => ['free' => '免费', 'pay' => '付费'],
            'payHint' => '付费时填写价格；试读比例 0–100；附件 URL 可为 /storage/... 或外链',
        ];
    }

    public function add(Request $request): View|JsonResponse
    {
        if ($request->isMethod('post')) {
            $tagNames = (string) $request->input('tag_names', '');
            $extraCats = (string) $request->input('extra_category_ids', '');
            $data = $this->validateSave($request);
            $model = $this->newModel();
            $model->fill($data);
            $model->save();
            $relations = app(ArchiveRelations::class);
            $relations->syncTags($model, $tagNames);
            $relations->syncExtraCategoryIds($model, $extraCats, (int) $model->category_id);
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

    public function edit(Request $request): View|JsonResponse
    {
        $ids = $request->input('ids', $request->route('ids'));
        $model = $this->findModel((int) $ids);
        if (! $model) {
            return response()->json(['code' => 0, 'msg' => '记录不存在'], 404);
        }

        if ($request->isMethod('post')) {
            $tagNames = (string) $request->input('tag_names', '');
            $extraCats = (string) $request->input('extra_category_ids', '');
            $data = $this->validateSave($request, $model);
            $model->fill($data);
            $model->save();
            $relations = app(ArchiveRelations::class);
            $relations->syncTags($model, $tagNames);
            $relations->syncExtraCategoryIds($model, $extraCats, (int) $model->category_id);
            $this->adminAuth->recordLog($request, $this->title, '编辑 ID:'.$model->getKey());

            return response()->json(['code' => 1, 'msg' => '保存成功']);
        }

        $relations = app(ArchiveRelations::class);
        $model->setAttribute('tag_names', $relations->tagNamesForForm($model));
        $model->setAttribute('extra_category_ids', $model->category_ids);

        return view('admin.partials.crud-form', [
            'title' => $this->title,
            'record' => $model,
            'formFields' => $this->formFields,
            'formExtras' => $this->formExtras(),
            'action' => url(config('cms.admin_path').'/'.$this->urlSlug().'/edit?ids='.$model->getKey()),
            'backUrl' => url(config('cms.admin_path').'/'.$this->urlSlug()),
        ]);
    }

    protected function validateSave(Request $request, ?Model $model = null): array
    {
        $id = $model?->getKey();

        $data = $request->validate([
            'category_id' => ['required', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:200'],
            'slug' => [
                'nullable', 'string', 'max:120',
                Rule::unique(Archive::class, 'slug')->ignore($id),
            ],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'keywords' => ['nullable', 'string', 'max:200'],
            'flag' => ['nullable', 'string', 'max:100'],
            'weigh' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,pending,normal,hidden,rejected'],
            'content' => ['nullable', 'string'],
            'pay_type' => ['required', 'in:free,pay'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'pay_ratio' => ['nullable', 'integer', 'min:0', 'max:100'],
            'download_url' => ['nullable', 'string', 'max:255'],
        ]);

        $slug = trim((string) ($data['slug'] ?? ''));
        if ($slug === '') {
            $slug = ArchiveSlug::fromTitle($data['title'], $id ? (int) $id : null);
        } else {
            $slug = ArchiveSlug::slugify($slug);
            if (! ArchiveSlug::validate($slug)) {
                throw ValidationException::withMessages([
                    'slug' => 'Slug 格式无效或为保留字',
                ]);
            }
            $slug = ArchiveSlug::ensureUnique($slug, $id ? (int) $id : null);
        }
        $data['slug'] = $slug;

        $data['category_id'] = (int) $data['category_id'];
        $data['weigh'] = (int) ($data['weigh'] ?? 0);
        $data['admin_id'] = $this->adminAuth->adminId() ?? 0;
        $data['pay_type'] = $data['pay_type'] ?? 'free';
        $data['price'] = $data['pay_type'] === 'pay' ? round((float) ($data['price'] ?? 0), 2) : 0;
        $data['pay_ratio'] = (int) ($data['pay_ratio'] ?? 0);
        $data['download_url'] = trim((string) ($data['download_url'] ?? ''));

        if ($data['status'] === 'normal') {
            $data['publishtime'] = $model?->publishtime ?: time();
        }

        if (array_key_exists('content', $data)) {
            $data['content'] = HtmlSanitizer::clean($data['content']);
        }

        return $data;
    }

    protected function transformRows(array $rows): array
    {
        return array_map(function ($row) {
            $arr = $row instanceof Model ? $row->toArray() : (array) $row;

            if ($row instanceof Archive) {
                if ($row->relationLoaded('category') && $row->category) {
                    $arr['category_id'] = $row->category->name.' (#'.$row->category_id.')';
                }
                if ($row->relationLoaded('tags') && $row->tags->isNotEmpty()) {
                    $arr['title'] = $arr['title'].' ['.$row->tags->pluck('name')->implode(', ').']';
                }
            }

            $arr['pay_type'] = ($arr['pay_type'] ?? 'free') === 'pay' ? '付费' : '免费';
            if (($arr['price'] ?? 0) > 0 && is_numeric($arr['price'])) {
                $arr['price'] = '¥'.number_format((float) $arr['price'], 2);
            }
            $arr['status'] = match ($arr['status'] ?? '') {
                'draft' => '草稿',
                'pending' => '待审核',
                'rejected' => '拒稿',
                'hidden' => '下线',
                default => '已发布',
            };
            if (! empty($arr['publishtime'])) {
                $arr['publishtime'] = date('Y-m-d H:i', (int) $arr['publishtime']);
            }

            return $arr;
        }, $rows);
    }
}
