<?php

namespace App\Http\Controllers\Admin\General;

use App\Http\Controllers\Admin\Controller;
use App\Models\Admin\Config;
use App\Services\Admin\AdminCache;
use App\Services\Admin\Dictionary;
use App\Support\AdminTableFilters;
use App\Support\BootstrapTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfigController extends Controller
{
    /** @var array<int, string> */
    protected array $allowedTypes = [
        'string', 'text', 'number', 'array', 'select', 'checkbox', 'radio', 'switch', 'image', 'file',
    ];

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson() || $request->has('limit') || $request->has('offset')) {
            $table = app(BootstrapTable::class);
            $query = Config::query()->orderBy('group')->orderBy('id');
            $result = $table->fetch($query, $request, ['name', 'title', 'group'], ['id', 'name', 'group', 'title', 'type', 'value']);

            $result['rows'] = array_map(function ($row) {
                $arr = $row instanceof Config ? $row->toArray() : (array) $row;
                $val = (string) ($arr['value'] ?? '');
                if (mb_strlen($val) > 60) {
                    $arr['value'] = mb_substr($val, 0, 60).'…';
                }

                return $arr;
            }, $result['rows']);

            return response()->json($result);
        }

        return view('admin.partials.crud-index', [
            'title' => '系统配置',
            'listFields' => ['id', 'name', 'group', 'title', 'type', 'value'],
            'urlPrefix' => 'general/config',
            'headingFilterField' => 'group',
            'headingFilterOptions' => $this->groupFilterOptions(),
            'filterFields' => array_merge(
                AdminTableFilters::fromListFields(['name', 'title', 'type', 'value']),
                ['group' => array_merge(AdminTableFilters::metaFor('group'), ['type' => 'text'])]
            ),
        ]);
    }

    public function add(Request $request): View|JsonResponse
    {
        if ($request->isMethod('post')) {
            $data = $this->validateConfig($request);
            $config = Config::query()->create($data);
            $this->flushConfigCache($config->name);
            $this->adminAuth->recordLog($request, '系统配置', '新增 '.$config->name);

            return response()->json(['code' => 1, 'msg' => '添加成功']);
        }

        return $this->formView(new Config(['group' => 'basic', 'type' => 'string', 'visible' => '']));
    }

    public function edit(Request $request): View|JsonResponse
    {
        $config = $this->findConfig((int) $request->input('ids', 0));
        if (! $config) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['code' => 0, 'msg' => '记录不存在'], 404);
            }
            abort(404);
        }

        if ($request->isMethod('post')) {
            $data = $this->validateConfig($request, $config);
            $config->fill($data);
            $config->save();
            $this->flushConfigCache($config->name);
            $this->adminAuth->recordLog($request, '系统配置', '编辑 '.$config->name);

            return response()->json(['code' => 1, 'msg' => '保存成功']);
        }

        return $this->formView($config);
    }

    public function del(Request $request): JsonResponse
    {
        $ids = $this->parseIds($request->input('ids', ''));
        if ($ids === []) {
            return response()->json(['code' => 0, 'msg' => '未选择记录']);
        }

        $names = Config::query()->whereIn('id', $ids)->pluck('name')->all();
        Config::query()->whereIn('id', $ids)->delete();
        foreach ($names as $name) {
            $this->flushConfigCache($name);
        }
        $this->adminAuth->recordLog($request, '系统配置', '删除: '.implode(',', $names));

        return response()->json(['code' => 1, 'msg' => '删除成功']);
    }

    /**
     * @deprecated Use edit instead; kept for backward-compatible API calls.
     */
    public function save(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'value' => ['nullable', 'string'],
        ]);

        $config = Config::query()->where('name', $payload['name'])->first();
        if (! $config) {
            return response()->json(['code' => 0, 'msg' => '配置不存在']);
        }
        $config->value = $payload['value'] ?? '';
        $config->save();
        $this->flushConfigCache($config->name);
        $this->adminAuth->recordLog($request, '系统配置', '更新值 '.$config->name);

        return response()->json(['code' => 1, 'msg' => '保存成功']);
    }

    protected function formView(Config $config): View
    {
        $adminPath = config('cms.admin_path', 'admin');

        return view('admin.general.config.form', [
            'title' => $config->exists ? '编辑配置' : '添加配置',
            'config' => $config,
            'typeOptions' => array_combine($this->allowedTypes, $this->allowedTypes),
            'groupOptions' => $this->distinctGroups(),
            'backUrl' => url($adminPath.'/general/config'),
            'action' => $config->exists
                ? url($adminPath.'/general/config/edit?ids='.$config->id)
                : url($adminPath.'/general/config/add'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateConfig(Request $request, ?Config $config = null): array
    {
        $nameRule = ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/'];
        if ($config) {
            $nameRule[] = 'unique:'.(new Config)->getTable().',name,'.$config->id;
        } else {
            $nameRule[] = 'unique:'.(new Config)->getTable().',name';
        }

        return $request->validate([
            'name' => $nameRule,
            'group' => ['required', 'string', 'max:30'],
            'title' => ['required', 'string', 'max:50'],
            'tip' => ['nullable', 'string', 'max:100'],
            'type' => ['required', 'in:'.implode(',', $this->allowedTypes)],
            'visible' => ['nullable', 'string', 'max:255'],
            'value' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'rule' => ['nullable', 'string', 'max:100'],
            'extend' => ['nullable', 'string', 'max:255'],
            'setting' => ['nullable', 'string', 'max:255'],
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected function groupFilterOptions(): array
    {
        $options = ['' => '全部'];
        foreach ($this->distinctGroups() as $group) {
            $options[$group] = $group;
        }

        return $options;
    }

    /**
     * @return array<int, string>
     */
    protected function distinctGroups(): array
    {
        return Config::query()
            ->select('group')
            ->distinct()
            ->orderBy('group')
            ->pluck('group')
            ->filter(fn ($g) => (string) $g !== '')
            ->values()
            ->all();
    }

    protected function findConfig(int $id): ?Config
    {
        return $id > 0 ? Config::query()->find($id) : null;
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

    protected function flushConfigCache(string $name): void
    {
        app(AdminCache::class)->forgetConfig();
        app(Dictionary::class)->flush($name);
    }
}
