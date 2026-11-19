<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\Test;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TestController extends CrudController
{
    protected string $modelClass = Test::class;

    protected string $title = '测试';

    protected array $listFields = ['id', 'category_id', 'title', 'weigh', 'switch', 'status', 'createtime'];

    protected array $searchFields = ['title'];

    protected array $formFields = [
        'admin_id' => 'selectpage:管理员',
        'category_id' => 'selectpage:分类',
        'title' => 'text:标题',
        'image' => 'image:封面',
        'images' => 'images:图片组',
        'weigh' => 'number:权重',
        'switch' => 'switch:开关',
        'status' => 'select:状态',
        'content' => 'editor:内容',
    ];

    protected bool $softDelete = true;

    protected string $softDeleteColumn = 'deletetime';

    protected ?string $headingFilterField = 'status';

    protected function baseQuery(): Builder
    {
        return parent::baseQuery()->with(['category', 'admin']);
    }

    /**
     * @return array<string, mixed>
     */
    protected function formExtras(): array
    {
        return [
            'statusOptions' => ['normal' => '正常', 'hidden' => '隐藏'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateSave(Request $request, ?Model $model = null): array
    {
        $data = $request->validate([
            'admin_id' => ['nullable', 'integer', 'min:0'],
            'category_id' => ['nullable', 'integer', 'min:0'],
            'title' => ['required', 'string', 'max:100'],
            'image' => ['nullable', 'string', 'max:255'],
            'images' => ['nullable', 'string', 'max:1500'],
            'weigh' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:normal,hidden'],
            'content' => ['nullable', 'string'],
        ]);

        $data['admin_id'] = (int) ($data['admin_id'] ?? 0);
        $data['category_id'] = (int) ($data['category_id'] ?? 0);
        $data['weigh'] = (int) ($data['weigh'] ?? 0);
        $data['switch'] = $request->boolean('switch') ? 1 : 0;

        return $data;
    }

    protected function transformRows(array $rows): array
    {
        return array_map(function ($row) {
            $arr = $row instanceof Model ? $row->toArray() : (array) $row;

            if ($row instanceof Test) {
                if ($row->relationLoaded('category') && $row->category) {
                    $arr['category_id'] = $row->category->name.' (#'.$row->category_id.')';
                }
                if ($row->relationLoaded('admin') && $row->admin) {
                    $arr['admin_id'] = $row->admin->nickname ?: $row->admin->username;
                }
            }

            $arr['switch'] = ! empty($arr['switch']) ? '开' : '关';
            $arr['status'] = ($arr['status'] ?? '') === 'hidden' ? '隐藏' : '正常';
            if (! empty($arr['createtime'])) {
                $arr['createtime'] = date('Y-m-d H:i', (int) $arr['createtime']);
            }

            return $arr;
        }, $rows);
    }
}
