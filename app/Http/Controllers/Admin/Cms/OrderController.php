<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Admin\CrudController;
use App\Models\Admin\Order;
use App\Support\AdminTableFilters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends CrudController
{
    protected string $modelClass = Order::class;

    protected string $title = '订单管理';

    protected array $listFields = ['id', 'order_sn', 'member_id', 'title', 'amount', 'pay_method', 'status', 'paytime', 'createtime'];

    protected array $searchFields = ['order_sn', 'title'];

    protected array $formFields = [
        'status' => 'select:状态',
    ];

    protected bool $softDelete = false;

    protected ?string $headingFilterField = 'status';

    /** @var array<string, string> */
    protected array $headingFilterOptions = [
        '' => '全部',
        'created' => '待支付',
        'paid' => '已支付',
        'closed' => '已关闭',
    ];

    public function add(Request $request): View|JsonResponse
    {
        abort(404);
    }

    public function del(Request $request): JsonResponse
    {
        abort(404);
    }

    protected function formExtras(): array
    {
        return [
            'statusOptions' => [
                'created' => '待支付',
                'paid' => '已支付',
                'closed' => '已关闭',
            ],
        ];
    }

    protected function validateSave(Request $request, ?Model $model = null): array
    {
        return $request->validate([
            'status' => ['required', 'in:created,paid,closed'],
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
            'showRecyclebin' => false,
            'filterFields' => AdminTableFilters::fromListFields($this->listFields),
            'showAdd' => false,
        ]);
    }

    protected function transformRows(array $rows): array
    {
        return array_map(function ($row) {
            $arr = $row instanceof Model ? $row->toArray() : (array) $row;

            $arr['status'] = match ($arr['status'] ?? '') {
                'created' => '待支付',
                'closed' => '已关闭',
                default => '已支付',
            };
            $arr['pay_method'] = match ($arr['pay_method'] ?? '') {
                'mock' => '模拟',
                default => '余额',
            };
            foreach (['paytime', 'createtime'] as $col) {
                if (! empty($arr[$col])) {
                    $arr[$col] = date('Y-m-d H:i', (int) $arr[$col]);
                }
            }
            $arr['amount'] = '¥'.number_format((float) ($arr['amount'] ?? 0), 2);

            return $arr;
        }, $rows);
    }
}
