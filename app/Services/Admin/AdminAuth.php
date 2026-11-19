<?php

namespace App\Services\Admin;

use App\Models\Admin\Admin;
use App\Models\Admin\AdminLog;
use App\Models\Admin\AuthRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AdminAuth
{
    public function __construct(
        protected string $sessionKey = ''
    ) {
        $this->sessionKey = config('cms.session_key', 'cms_admin');
    }

    public function login(Admin $admin, Request $request): void
    {
        $admin->token = bin2hex(random_bytes(16));
        $admin->logintime = time();
        $admin->loginip = $request->ip() ?? '';
        $admin->loginfailure = 0;
        $admin->save();

        Session::put($this->sessionKey, [
            'id' => $admin->id,
            'username' => $admin->username,
            'nickname' => $admin->nickname,
            'avatar' => $admin->avatar,
            'token' => $admin->token,
        ]);
    }

    public function logout(): void
    {
        Session::forget($this->sessionKey);
    }

    public function user(): ?array
    {
        $data = Session::get($this->sessionKey);

        return is_array($data) ? $data : null;
    }

    public function adminId(): ?int
    {
        $user = $this->user();

        return $user['id'] ?? null;
    }

    public function adminModel(): ?Admin
    {
        $id = $this->adminId();
        if (! $id) {
            return null;
        }

        return Admin::query()->find($id);
    }

    public function ruleIds(): array
    {
        $admin = $this->adminModel();
        if (! $admin) {
            return [];
        }

        $admin->load('groups');
        $ids = [];
        foreach ($admin->groups as $group) {
            if ($group->status !== 'normal') {
                continue;
            }
            $groupIds = $group->ruleIds();
            if (in_array('*', $groupIds, true)) {
                return ['*'];
            }
            $ids = array_merge($ids, $groupIds);
        }

        return array_values(array_unique($ids));
    }

    public function ruleNames(): array
    {
        $ids = $this->ruleIds();
        if (in_array('*', $ids, true)) {
            return ['*'];
        }
        if ($ids === []) {
            return [];
        }

        return AuthRule::query()
            ->whereIn('id', $ids)
            ->where('status', 'normal')
            ->pluck('name')
            ->map(fn ($name) => strtolower((string) $name))
            ->all();
    }

    public function check(string $permission): bool
    {
        $permission = strtolower($permission);
        $names = $this->ruleNames();
        if (in_array('*', $names, true)) {
            return true;
        }

        if (in_array($permission, $names, true)) {
            return true;
        }

        // 拥有「本地插件 / 在线命令」菜单权限即可执行其安装、启用等子操作（避免只勾选菜单节点时按钮无效）
        foreach ([
            'general/addon/index' => 'general/addon/',
            'general/command/index' => 'general/command/',
            'general/cache/index' => 'general/cache/',
        ] as $menuRule => $prefix) {
            if (str_starts_with($permission, $prefix) && $permission !== $menuRule && in_array($menuRule, $names, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function menuTree(): array
    {
        $rules = collect(app(AdminCache::class)->authRulesModels())
            ->filter(fn (AuthRule $rule) => $rule->status === 'normal' && ! empty($rule->ismenu));
        $names = $this->ruleNames();
        $isSuper = in_array('*', $names, true);

        if (! $isSuper) {
            $rules = $rules->filter(function (AuthRule $rule) use ($names) {
                return in_array(strtolower($rule->name), $names, true);
            });
        }

        return $this->buildTree($rules->values()->all());
    }

    /**
     * @param  array<int, AuthRule>  $rules
     * @return array<int, array<string, mixed>>
     */
    protected function buildTree(array $rules, int $pid = 0): array
    {
        $branch = [];
        foreach ($rules as $rule) {
            if ((int) $rule->pid !== $pid) {
                continue;
            }
            $item = $rule->only(['id', 'pid', 'name', 'title', 'icon', 'url', 'menutype']);
            $children = $this->buildTree($rules, (int) $rule->id);
            if ($children !== []) {
                $item['children'] = $children;
            }
            $branch[] = $item;
        }

        return $branch;
    }

    public function recordLog(Request $request, string $title, string $content = ''): void
    {
        $user = $this->user();
        if (! $user) {
            return;
        }

        AdminLog::query()->create([
            'admin_id' => $user['id'],
            'username' => $user['username'],
            'url' => substr($request->fullUrl(), 0, 1500),
            'title' => $title,
            'content' => $content,
            'ip' => $request->ip() ?? '',
            'useragent' => substr((string) $request->userAgent(), 0, 255),
            'createtime' => time(),
        ]);
    }
}
