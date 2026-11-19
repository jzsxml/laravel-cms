<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Admin\Controller;
use App\Models\Admin\AuthGroup;
use App\Support\AdminRuleTree;
use App\Support\AdminTableFilters;
use App\Support\BootstrapTable;
use App\Support\CmsTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson() || $request->has('limit') || $request->has('offset')) {
            $table = app(BootstrapTable::class);
            $query = AuthGroup::query();
            $result = $table->fetch($query, $request, ['name'], ['id', 'pid', 'name', 'rules', 'status']);

            $result['rows'] = array_map(function ($row) {
                $arr = $row instanceof AuthGroup ? $row->toArray() : (array) $row;
                $arr['rules_label'] = $this->rulesLabel((string) ($arr['rules'] ?? ''));

                return $arr;
            }, $result['rows']);

            return response()->json($result);
        }

        return view('admin.partials.crud-index', [
            'title' => '角色组',
            'listFields' => ['id', 'pid', 'name', 'rules_label', 'status'],
            'urlPrefix' => 'auth/group',
            'headingFilterField' => 'status',
            'headingFilterOptions' => [
                '' => '全部',
                'normal' => '正常',
                'hidden' => '隐藏',
            ],
            'filterFields' => AdminTableFilters::fromListFields(['pid', 'name', 'rules', 'status']),
        ]);
    }

    public function add(Request $request): View|JsonResponse
    {
        if ($request->isMethod('post')) {
            $data = $this->validateGroup($request);
            $group = AuthGroup::query()->create($data);
            $this->adminAuth->recordLog($request, '角色组', '新增 ID:'.$group->id);

            return response()->json(['code' => 1, 'msg' => '添加成功']);
        }

        return $this->formView($request, new AuthGroup(['pid' => 0, 'status' => 'normal', 'rules' => '']));
    }

    public function edit(Request $request): View|JsonResponse
    {
        $group = $this->findGroup((int) $request->input('ids', 0));
        if (! $group) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['code' => 0, 'msg' => '记录不存在'], 404);
            }
            abort(404);
        }

        if ($request->isMethod('post')) {
            if ($this->isSuperGroup($group) && $this->normalizeRulesFromRequest($request) !== '*') {
                return response()->json(['code' => 0, 'msg' => '超级管理员组必须保持全部权限']);
            }
            $data = $this->validateGroup($request, $group);
            $group->fill($data);
            $group->save();
            $this->adminAuth->recordLog($request, '角色组', '编辑 ID:'.$group->id);

            return response()->json(['code' => 1, 'msg' => '保存成功']);
        }

        return $this->formView($request, $group);
    }

    public function del(Request $request): JsonResponse
    {
        $ids = $this->parseIds($request->input('ids', ''));
        if ($ids === []) {
            return response()->json(['code' => 0, 'msg' => '未选择记录']);
        }

        if (in_array(1, $ids, true)) {
            return response()->json(['code' => 0, 'msg' => '不能删除超级管理员组']);
        }

        $inUse = DB::table(CmsTable::name('auth_group_access'))
            ->whereIn('group_id', $ids)
            ->exists();
        if ($inUse) {
            return response()->json(['code' => 0, 'msg' => '仍有管理员使用该角色组，无法删除']);
        }

        AuthGroup::query()->whereIn('id', $ids)->delete();
        $this->adminAuth->recordLog($request, '角色组', '删除: '.implode(',', $ids));

        return response()->json(['code' => 1, 'msg' => '删除成功']);
    }

    protected function formView(Request $request, AuthGroup $group): View
    {
        $adminPath = config('cms.admin_path', 'admin');
        $isSuper = $this->isSuperGroup($group);
        $selectedIds = $isSuper ? [] : $group->ruleIds();

        $parentOptions = AuthGroup::query()
            ->when($group->exists, fn ($q) => $q->where('id', '!=', $group->id))
            ->orderBy('id')
            ->pluck('name', 'id')
            ->all();

        return view('admin.auth.group.form', [
            'title' => $group->exists ? '编辑角色组' : '添加角色组',
            'group' => $group,
            'ruleTree' => AdminRuleTree::build(),
            'selectedRuleIds' => $selectedIds,
            'rulesAll' => $isSuper,
            'parentOptions' => $parentOptions,
            'backUrl' => url($adminPath.'/auth/group'),
            'action' => $group->exists
                ? url($adminPath.'/auth/group/edit?ids='.$group->id)
                : url($adminPath.'/auth/group/add'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateGroup(Request $request, ?AuthGroup $group = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'pid' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:normal,hidden'],
        ]);

        $pid = (int) ($data['pid'] ?? 0);
        if ($group && $pid === (int) $group->id) {
            $pid = 0;
        }
        $data['pid'] = $pid;
        $data['rules'] = $this->normalizeRulesFromRequest($request);

        return $data;
    }

    protected function normalizeRulesFromRequest(Request $request): string
    {
        if ($request->boolean('rules_all')) {
            return '*';
        }

        $ids = $request->input('rule_ids', []);
        if (! is_array($ids)) {
            $ids = array_filter(array_map('intval', explode(',', (string) $ids)));
        } else {
            $ids = array_values(array_filter(array_map('intval', $ids)));
        }

        if ($ids === []) {
            return '';
        }

        return implode(',', array_unique($ids));
    }

    protected function rulesLabel(string $rules): string
    {
        if ($rules === '*' || trim($rules) === '*') {
            return '全部';
        }
        $count = count(array_filter(explode(',', $rules)));

        return $count > 0 ? "{$count} 条规则" : '未分配';
    }

    protected function isSuperGroup(AuthGroup $group): bool
    {
        return trim((string) $group->rules) === '*';
    }

    protected function findGroup(int $id): ?AuthGroup
    {
        return $id > 0 ? AuthGroup::query()->find($id) : null;
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
}
