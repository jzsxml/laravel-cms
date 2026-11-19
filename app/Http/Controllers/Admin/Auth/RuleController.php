<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Admin\Controller;
use App\Models\Admin\AuthGroup;
use App\Models\Admin\AuthRule;
use App\Services\Admin\AdminCache;
use App\Support\AdminTableFilters;
use App\Support\BootstrapTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RuleController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson() || $request->has('limit') || $request->has('offset')) {
            $table = app(BootstrapTable::class);
            $query = AuthRule::query()->orderByDesc('weigh')->orderBy('id');
            $result = $table->fetch($query, $request, ['name', 'title'], ['id', 'pid', 'name', 'title', 'icon', 'ismenu', 'weigh', 'status']);

            $result['rows'] = array_map(function ($row) {
                $arr = $row instanceof AuthRule ? $row->toArray() : (array) $row;
                $arr['ismenu'] = ! empty($arr['ismenu']) ? '是' : '否';

                return $arr;
            }, $result['rows']);

            return response()->json($result);
        }

        return view('admin.partials.crud-index', [
            'title' => '菜单规则',
            'listFields' => ['id', 'pid', 'name', 'title', 'icon', 'ismenu', 'weigh', 'status'],
            'urlPrefix' => 'auth/rule',
            'headingFilterField' => 'status',
            'headingFilterOptions' => [
                '' => '全部',
                'normal' => '正常',
                'hidden' => '隐藏',
            ],
            'filterFields' => AdminTableFilters::fromListFields(['pid', 'name', 'title', 'icon', 'weigh', 'status']),
        ]);
    }

    public function add(Request $request): View|JsonResponse
    {
        if ($request->isMethod('post')) {
            $data = $this->validateRule($request);
            $rule = AuthRule::query()->create($data);
            $this->syncPinyin($rule);
            $rule->save();
            app(AdminCache::class)->forgetAuthRules();
            $this->adminAuth->recordLog($request, '菜单规则', '新增 ID:'.$rule->id);

            return response()->json(['code' => 1, 'msg' => '添加成功']);
        }

        return $this->formView($request, new AuthRule([
            'pid' => (int) $request->input('pid', 0),
            'type' => 'file',
            'status' => 'normal',
            'weigh' => 0,
            'ismenu' => 0,
        ]));
    }

    public function edit(Request $request): View|JsonResponse
    {
        $rule = $this->findRule((int) $request->input('ids', 0));
        if (! $rule) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['code' => 0, 'msg' => '记录不存在'], 404);
            }
            abort(404);
        }

        if ($request->isMethod('post')) {
            $data = $this->validateRule($request, $rule);
            $rule->fill($data);
            $this->syncPinyin($rule);
            $rule->save();
            app(AdminCache::class)->forgetAuthRules();
            $this->adminAuth->recordLog($request, '菜单规则', '编辑 ID:'.$rule->id);

            return response()->json(['code' => 1, 'msg' => '保存成功']);
        }

        return $this->formView($request, $rule);
    }

    public function del(Request $request): JsonResponse
    {
        $ids = $this->parseIds($request->input('ids', ''));
        if ($ids === []) {
            return response()->json(['code' => 0, 'msg' => '未选择记录']);
        }

        $protected = ['index/index'];
        foreach ($ids as $id) {
            $rule = AuthRule::query()->find($id);
            if (! $rule) {
                continue;
            }
            if (in_array($rule->name, $protected, true)) {
                return response()->json(['code' => 0, 'msg' => '不能删除系统规则: '.$rule->name]);
            }
            if (AuthRule::query()->where('pid', $id)->exists()) {
                return response()->json(['code' => 0, 'msg' => "规则「{$rule->title}」存在子节点，请先删除子规则"]);
            }
        }

        AuthRule::query()->whereIn('id', $ids)->delete();
        $this->detachRulesFromGroups($ids);
        app(AdminCache::class)->forgetAuthRules();
        $this->adminAuth->recordLog($request, '菜单规则', '删除: '.implode(',', $ids));

        return response()->json(['code' => 1, 'msg' => '删除成功']);
    }

    protected function formView(Request $request, AuthRule $rule): View
    {
        $adminPath = config('cms.admin_path', 'admin');
        $excludeIds = $rule->exists ? array_merge([$rule->id], $this->descendantIds($rule->id)) : [];

        $parentOptions = AuthRule::query()
            ->when($excludeIds !== [], fn ($q) => $q->whereNotIn('id', $excludeIds))
            ->orderByDesc('weigh')
            ->orderBy('id')
            ->get()
            ->mapWithKeys(fn (AuthRule $r) => [$r->id => $r->title.' ('.$r->name.')'])
            ->all();

        return view('admin.auth.rule.form', [
            'title' => $rule->exists ? '编辑菜单规则' : '添加菜单规则',
            'rule' => $rule,
            'parentOptions' => $parentOptions,
            'backUrl' => url($adminPath.'/auth/rule'),
            'action' => $rule->exists
                ? url($adminPath.'/auth/rule/edit?ids='.$rule->id)
                : url($adminPath.'/auth/rule/add'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateRule(Request $request, ?AuthRule $rule = null): array
    {
        $nameRule = ['required', 'string', 'max:100', 'regex:/^[a-z0-9_\/]+$/'];
        if ($rule) {
            $nameRule[] = 'unique:'.(new AuthRule)->getTable().',name,'.$rule->id;
        } else {
            $nameRule[] = 'unique:'.(new AuthRule)->getTable().',name';
        }

        $data = $request->validate([
            'pid' => ['nullable', 'integer', 'min:0'],
            'type' => ['required', 'in:menu,file'],
            'name' => $nameRule,
            'title' => ['required', 'string', 'max:50'],
            'icon' => ['nullable', 'string', 'max:50'],
            'url' => ['nullable', 'string', 'max:255'],
            'remark' => ['nullable', 'string', 'max:255'],
            'ismenu' => ['nullable', 'boolean'],
            'menutype' => ['nullable', 'in:addtabs,blank,dialog,ajax'],
            'weigh' => ['nullable', 'integer'],
            'status' => ['required', 'in:normal,hidden'],
        ]);

        $pid = (int) ($data['pid'] ?? 0);
        if ($rule) {
            $invalidParents = array_merge([$rule->id], $this->descendantIds($rule->id));
            if (in_array($pid, $invalidParents, true)) {
                $pid = (int) $rule->pid;
            }
        }
        $data['pid'] = $pid;
        $data['ismenu'] = $request->boolean('ismenu') ? 1 : 0;
        $data['weigh'] = (int) ($data['weigh'] ?? 0);
        $data['icon'] = $data['icon'] ?? '';
        $data['url'] = $data['url'] ?? '';
        $data['remark'] = $data['remark'] ?? '';
        if ($data['ismenu'] === 0) {
            $data['menutype'] = null;
        }

        return $data;
    }

    protected function syncPinyin(AuthRule $rule): void
    {
        $title = preg_replace('/\s+/', '', (string) $rule->title) ?? (string) $rule->title;
        $rule->py = Str::lower(Str::substr($title, 0, 8));
        $rule->pinyin = Str::lower($title);
    }

    /**
     * @param  array<int, int>  $removeIds
     */
    protected function detachRulesFromGroups(array $removeIds): void
    {
        AuthGroup::query()->where('rules', '!=', '*')->chunkById(50, function ($groups) use ($removeIds) {
            foreach ($groups as $group) {
                $ids = $group->ruleIds();
                if ($ids === [] || in_array('*', $ids, true)) {
                    continue;
                }
                $next = array_values(array_diff($ids, $removeIds));
                if ($next === $ids) {
                    continue;
                }
                $group->rules = $next === [] ? '' : implode(',', $next);
                $group->save();
            }
        });
    }

    /**
     * @return array<int, int>
     */
    protected function descendantIds(int $id): array
    {
        $rows = AuthRule::query()->get(['id', 'pid']);
        $out = [];
        $this->collectDescendants($rows->all(), $id, $out);

        return $out;
    }

    /**
     * @param  array<int, AuthRule>  $rows
     * @param  array<int, int>  $out
     */
    protected function collectDescendants(array $rows, int $parentId, array &$out): void
    {
        foreach ($rows as $row) {
            if ((int) $row->pid !== $parentId) {
                continue;
            }
            $out[] = (int) $row->id;
            $this->collectDescendants($rows, (int) $row->id, $out);
        }
    }

    protected function findRule(int $id): ?AuthRule
    {
        return $id > 0 ? AuthRule::query()->find($id) : null;
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
