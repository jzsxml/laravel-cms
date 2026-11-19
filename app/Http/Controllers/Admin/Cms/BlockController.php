<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Admin\CrudController;
use App\Models\Admin\CmsBlock as BlockModel;
use App\Support\CmsBlock;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlockController extends CrudController
{
    protected string $modelClass = BlockModel::class;

    protected string $title = '区块管理';

    protected array $listFields = ['id', 'name', 'title', 'type', 'status', 'weigh'];

    protected array $searchFields = ['name', 'title'];

    protected array $formFields = [
        'name' => 'text:调用标识',
        'title' => 'text:标题',
        'type' => 'select:类型',
        'content' => 'textarea:内容',
        'weigh' => 'number:权重',
        'status' => 'select:状态',
    ];

    protected function formExtras(): array
    {
        return [
            'typeOptions' => ['html' => 'HTML', 'image' => '图片URL', 'text' => '纯文本'],
            'statusOptions' => ['normal' => '正常', 'hidden' => '隐藏'],
            'nameHint' => '模板调用 CmsBlock::html(\'name\')',
        ];
    }

    protected function validateSave(Request $request, ?Model $model = null): array
    {
        $id = $model?->getKey();

        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:80', 'regex:/^[a-z0-9_-]+$/',
                Rule::unique(BlockModel::class, 'name')->ignore($id),
            ],
            'title' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:html,image,text'],
            'content' => ['nullable', 'string'],
            'weigh' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:normal,hidden'],
        ]);

        if (($data['type'] ?? '') === 'html' && array_key_exists('content', $data)) {
            $data['content'] = HtmlSanitizer::clean($data['content']);
        }

        return $data;
    }

    public function add(Request $request): JsonResponse|View
    {
        $response = parent::add($request);
        if ($request->isMethod('post')) {
            $name = (string) $request->input('name', '');
            if ($name !== '') {
                CmsBlock::forget($name);
            }
        }

        return $response;
    }

    public function edit(Request $request): JsonResponse|View
    {
        $response = parent::edit($request);
        if ($request->isMethod('post')) {
            $name = (string) $request->input('name', '');
            if ($name !== '') {
                CmsBlock::forget($name);
            }
        }

        return $response;
    }
}
