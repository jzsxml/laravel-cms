<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Admin\CrudController;
use App\Models\Admin\CmsModel;
use App\Services\Cms\ModelSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ModelDesignerController extends CrudController
{
    protected string $modelClass = CmsModel::class;

    protected string $title = '模型设计';

    protected array $listFields = ['id', 'name', 'title', 'status', 'weigh', 'updatetime'];

    protected array $searchFields = ['name', 'title'];

    protected array $formFields = [
        'name' => 'text:标识',
        'title' => 'text:名称',
        'fields' => 'textarea:字段JSON',
        'weigh' => 'number:权重',
        'status' => 'select:状态',
    ];

    protected function formExtras(): array
    {
        return [
            'statusOptions' => ['normal' => '正常', 'hidden' => '隐藏'],
            'nameHint' => '英文标识，如 product；数据管理入口在「模型数据」',
            'fieldsHint' => '[{"name":"title","label":"标题","type":"text","list":true,"required":true},{"name":"price","label":"价格","type":"number","list":true}]',
        ];
    }

    protected function validateSave(Request $request, ?Model $model = null): array
    {
        $id = $model?->getKey();
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:60', 'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique(CmsModel::class, 'name')->ignore($id),
            ],
            'title' => ['required', 'string', 'max:100'],
            'fields' => ['required', 'string'],
            'weigh' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:normal,hidden'],
        ]);

        try {
            $data['fields'] = app(ModelSchema::class)->parseFieldsJson($data['fields']);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\JsonException) {
            throw ValidationException::withMessages(['fields' => 'JSON 格式无效']);
        }

        $data['weigh'] = (int) ($data['weigh'] ?? 0);

        return $data;
    }
}
