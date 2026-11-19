<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Admin\CrudController;
use App\Models\Admin\Comment;
use App\Support\AdminTableFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends CrudController
{
    protected string $modelClass = Comment::class;

    protected string $title = '评论管理';

    protected array $listFields = ['id', 'archive_id', 'member_nickname', 'content', 'status', 'createtime'];

    protected array $searchFields = ['content', 'member_nickname'];

    protected array $formFields = [
        'status' => 'select:状态',
    ];

    protected bool $softDelete = true;

    protected string $softDeleteColumn = 'deletetime';

    protected ?string $headingFilterField = 'status';

    protected function baseQuery(): Builder
    {
        return parent::baseQuery()->with(['archive']);
    }

    protected function formExtras(): array
    {
        return [
            'statusOptions' => ['normal' => '正常', 'hidden' => '隐藏'],
        ];
    }

    protected function validateSave(Request $request, ?Model $model = null): array
    {
        return $request->validate([
            'status' => ['required', 'in:normal,hidden'],
        ]);
    }

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
            'showAdd' => false,
        ]);
    }

    public function add(Request $request): View|JsonResponse
    {
        abort(404);
    }

    protected function transformRows(array $rows): array
    {
        return array_map(function ($row) {
            $arr = $row instanceof Model ? $row->toArray() : (array) $row;
            if ($row instanceof Comment && $row->relationLoaded('archive') && $row->archive) {
                $arr['archive_id'] = $row->archive->title.' (#'.$row->archive_id.')';
            }
            $arr['content'] = mb_strlen($arr['content'] ?? '') > 60
                ? mb_substr($arr['content'], 0, 60).'…'
                : ($arr['content'] ?? '');
            if (! empty($arr['createtime'])) {
                $arr['createtime'] = date('Y-m-d H:i', (int) $arr['createtime']);
            }
            $arr['status'] = ($arr['status'] ?? '') === 'hidden' ? '隐藏' : '正常';

            return $arr;
        }, $rows);
    }
}
