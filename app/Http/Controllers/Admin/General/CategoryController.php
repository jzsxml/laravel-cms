<?php

namespace App\Http\Controllers\Admin\General;

use App\Http\Controllers\Admin\CrudController;
use App\Models\Admin\Category;
use App\Services\Admin\AdminCache;
use App\Services\Admin\Dictionary;
use Illuminate\Http\Request;

class CategoryController extends CrudController
{
    protected string $modelClass = Category::class;

    protected string $title = '分类管理';

    protected array $listFields = ['id', 'pid', 'type', 'name', 'weigh', 'status'];

    protected array $searchFields = ['name', 'nickname'];

    protected array $formFields = [
        'pid' => 'selectpage:父级分类',
        'type' => 'select:分类类型',
        'name' => 'text:名称',
        'nickname' => 'text:别名',
        'image' => 'image:图片',
        'keywords' => 'text:关键字',
        'description' => 'text:描述',
        'weigh' => 'number:权重',
        'status' => 'select:状态',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function formExtras(): array
    {
        $dict = app(Dictionary::class);

        return [
            'typeOptions' => $dict->get('categorytype'),
            'statusOptions' => ['normal' => '正常', 'hidden' => '隐藏'],
        ];
    }

    protected function afterMutation(Request $request, string $action): void
    {
        app(AdminCache::class)->forgetCategories();
    }
}
