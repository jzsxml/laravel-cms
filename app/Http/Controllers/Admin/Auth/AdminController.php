<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Admin\Controller;
use App\Models\Admin\Admin;
use App\Models\Admin\AuthGroup;
use App\Support\AdminTableFilters;
use App\Support\BootstrapTable;
use App\Support\CmsTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson() || $request->has('limit') || $request->has('offset')) {
            $table = app(BootstrapTable::class);
            $query = Admin::query()->with('groups');
            $result = $table->fetch($query, $request, ['username', 'nickname', 'email', 'mobile'], ['id', 'username', 'nickname', 'email', 'mobile', 'status', 'logintime']);

            $result['rows'] = array_map(function ($row) {
                $arr = $row instanceof Admin ? $row->toArray() : (array) $row;
                if ($row instanceof Admin && $row->relationLoaded('groups')) {
                    $arr['groups'] = $row->groups->pluck('name')->implode(', ');
                }
                if (! empty($arr['logintime'])) {
                    $arr['logintime'] = date('Y-m-d H:i', (int) $arr['logintime']);
                }
                $arr['status'] = ($arr['status'] ?? '') === 'hidden' ? '隐藏' : '正常';

                return $arr;
            }, $result['rows']);

            return response()->json($result);
        }

        return view('admin.partials.crud-index', [
            'title' => '管理员',
            'listFields' => ['id', 'username', 'nickname', 'groups', 'email', 'mobile', 'status', 'logintime'],
            'urlPrefix' => 'auth/admin',
            'headingFilterField' => 'status',
            'headingFilterOptions' => ['' => '全部', 'normal' => '正常', 'hidden' => '隐藏'],
            'showRecyclebin' => false,
            'filterFields' => AdminTableFilters::fromListFields(['username', 'nickname', 'email', 'mobile', 'status']),
        ]);
    }

    public function add(Request $request): View|JsonResponse
    {
        if ($request->isMethod('post')) {
            $data = $this->validateAdmin($request);
            $salt = bin2hex(random_bytes(8));
            $data['salt'] = $salt;
            $data['password'] = Admin::encryptPassword($data['password'], $salt);
            $groupIds = $data['group_ids'];
            unset($data['group_ids'], $data['password_confirm']);

            $admin = Admin::query()->create($data);
            $this->syncGroups($admin, $groupIds);
            $this->adminAuth->recordLog($request, '管理员', '新增 ID:'.$admin->id);

            return response()->json(['code' => 1, 'msg' => '添加成功']);
        }

        return $this->formView($request, new Admin(['status' => 'normal']), []);
    }

    public function edit(Request $request): View|JsonResponse
    {
        $admin = $this->findAdmin((int) $request->input('ids', 0));
        if (! $admin) {
            abort(404);
        }

        if ($request->isMethod('post')) {
            $data = $this->validateAdmin($request, $admin);
            $groupIds = $data['group_ids'];
            unset($data['group_ids'], $data['password_confirm']);

            if (! empty($data['password'])) {
                $salt = bin2hex(random_bytes(8));
                $data['salt'] = $salt;
                $data['password'] = Admin::encryptPassword($data['password'], $salt);
            } else {
                unset($data['password']);
            }

            if ((int) $admin->id === 1 && ($data['status'] ?? '') === 'hidden') {
                return response()->json(['code' => 0, 'msg' => '不能禁用超级管理员']);
            }

            $admin->fill($data);
            $admin->save();
            $this->syncGroups($admin, $groupIds);
            $this->adminAuth->recordLog($request, '管理员', '编辑 ID:'.$admin->id);

            return response()->json(['code' => 1, 'msg' => '保存成功']);
        }

        $groupIds = $admin->groups->pluck('id')->all();

        return $this->formView($request, $admin, $groupIds);
    }

    public function del(Request $request): JsonResponse
    {
        $ids = $this->parseIds($request->input('ids', ''));
        if ($ids === []) {
            return response()->json(['code' => 0, 'msg' => '未选择记录']);
        }
        if (in_array(1, $ids, true)) {
            return response()->json(['code' => 0, 'msg' => '不能删除超级管理员']);
        }
        $self = $this->adminAuth->adminId();
        if ($self && in_array($self, $ids, true)) {
            return response()->json(['code' => 0, 'msg' => '不能删除当前登录账号']);
        }

        DB::table(CmsTable::name('auth_group_access'))->whereIn('uid', $ids)->delete();
        Admin::query()->whereIn('id', $ids)->delete();
        $this->adminAuth->recordLog($request, '管理员', '删除: '.implode(',', $ids));

        return response()->json(['code' => 1, 'msg' => '删除成功']);
    }

    /**
     * @param  array<int, int>  $selectedGroupIds
     */
    protected function formView(Request $request, Admin $admin, array $selectedGroupIds): View
    {
        $groups = AuthGroup::query()->where('status', 'normal')->orderBy('id')->get();

        return view('admin.auth.admin.form', [
            'title' => $admin->exists ? '编辑管理员' : '添加管理员',
            'admin' => $admin,
            'groups' => $groups,
            'selectedGroupIds' => $selectedGroupIds,
            'backUrl' => url(config('cms.admin_path', 'admin').'/auth/admin'),
            'action' => $admin->exists
                ? url(config('cms.admin_path', 'admin').'/auth/admin/edit?ids='.$admin->id)
                : url(config('cms.admin_path', 'admin').'/auth/admin/add'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateAdmin(Request $request, ?Admin $admin = null): array
    {
        $usernameRule = ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_]+$/'];
        if ($admin) {
            $usernameRule[] = 'unique:'.(new Admin)->getTable().',username,'.$admin->id;
        } else {
            $usernameRule[] = 'unique:'.(new Admin)->getTable().',username';
        }

        $rules = [
            'username' => $usernameRule,
            'nickname' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:normal,hidden'],
            'group_ids' => ['required', 'array', 'min:1'],
            'group_ids.*' => ['integer', 'exists:'.CmsTable::name('auth_group').',id'],
            'password' => [$admin ? 'nullable' : 'required', 'string', 'min:6', 'max:32'],
            'password_confirm' => ['nullable', 'same:password'],
        ];

        return $request->validate($rules);
    }

    /**
     * @param  array<int, int>  $groupIds
     */
    protected function syncGroups(Admin $admin, array $groupIds): void
    {
        DB::table(CmsTable::name('auth_group_access'))->where('uid', $admin->id)->delete();
        $rows = [];
        foreach (array_unique(array_map('intval', $groupIds)) as $gid) {
            if ($gid > 0) {
                $rows[] = ['uid' => $admin->id, 'group_id' => $gid];
            }
        }
        if ($rows !== []) {
            DB::table(CmsTable::name('auth_group_access'))->insert($rows);
        }
    }

    protected function findAdmin(int $id): ?Admin
    {
        return $id > 0 ? Admin::query()->find($id) : null;
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
