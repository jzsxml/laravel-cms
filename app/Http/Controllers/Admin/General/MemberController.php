<?php

namespace App\Http\Controllers\Admin\General;

use App\Http\Controllers\Admin\CrudController;
use App\Models\Api\Member;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class MemberController extends CrudController
{
    protected string $modelClass = Member::class;

    protected string $title = '会员管理';

    protected array $listFields = ['id', 'username', 'nickname', 'money', 'email', 'mobile', 'status', 'logintime', 'createtime'];

    protected array $searchFields = ['username', 'nickname', 'email', 'mobile'];

    protected array $formFields = [
        'username' => 'text:用户名',
        'nickname' => 'text:昵称',
        'password' => 'text:密码',
        'email' => 'text:邮箱',
        'mobile' => 'text:手机',
        'avatar' => 'image:头像',
        'money' => 'number:余额',
        'vip_expiretime' => 'text:VIP到期时间戳',
        'status' => 'select:状态',
    ];

    protected ?string $headingFilterField = 'status';

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
        $rules = [
            'username' => ['required', 'string', 'max:50'],
            'nickname' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'string', 'max:255'],
            'money' => ['nullable', 'numeric', 'min:0'],
            'vip_expiretime' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:normal,hidden'],
            'password' => [$model ? 'nullable' : 'required', 'string', 'min:6', 'max:32'],
        ];
        if (! $model) {
            $rules['username'][] = 'unique:'.(new Member)->getTable().',username';
        } else {
            $rules['username'][] = 'unique:'.(new Member)->getTable().',username,'.$model->getKey();
        }

        $data = $request->validate($rules);

        if (! empty($data['password'])) {
            $salt = bin2hex(random_bytes(8));
            $data['salt'] = $salt;
            $data['password'] = Member::encryptPassword($data['password'], $salt);
        } else {
            unset($data['password']);
        }

        if (array_key_exists('money', $data)) {
            $data['money'] = round((float) $data['money'], 2);
        }
        if (array_key_exists('vip_expiretime', $data)) {
            $data['vip_expiretime'] = $data['vip_expiretime'] !== null && $data['vip_expiretime'] !== ''
                ? (int) $data['vip_expiretime']
                : null;
        }

        return $data;
    }

    protected function findModel(int $id): ?Model
    {
        return Member::query()->find($id);
    }
}
