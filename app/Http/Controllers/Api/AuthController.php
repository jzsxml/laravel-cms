<?php

namespace App\Http\Controllers\Api;

use App\Models\Api\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ]);

        $member = Member::query()->where('username', $data['username'])->first();
        if (! $member || $member->status !== 'normal' || ! $member->verifyPassword($data['password'])) {
            return response()->json(['code' => 0, 'msg' => '用户名或密码错误']);
        }

        $member->token = bin2hex(random_bytes(16));
        $member->logintime = time();
        $member->loginip = $request->ip() ?? '';
        $member->save();

        return response()->json([
            'code' => 1,
            'msg' => '登录成功',
            'data' => [
                'token' => $member->token,
                'userinfo' => $member->only(['id', 'username', 'nickname', 'avatar', 'email', 'mobile']),
            ],
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:50', 'alpha_dash'],
            'password' => ['required', 'string', 'min:6'],
            'email' => ['nullable', 'email'],
        ]);

        if (Member::query()->where('username', $data['username'])->exists()) {
            return response()->json(['code' => 0, 'msg' => '用户名已存在']);
        }

        $salt = bin2hex(random_bytes(8));
        $member = Member::query()->create([
            'username' => $data['username'],
            'nickname' => $data['username'],
            'password' => Member::encryptPassword($data['password'], $salt),
            'salt' => $salt,
            'email' => $data['email'] ?? '',
            'status' => 'normal',
        ]);

        return response()->json(['code' => 1, 'msg' => '注册成功', 'data' => ['id' => $member->id]]);
    }

    public function profile(Request $request): JsonResponse
    {
        $member = $this->member($request);

        return response()->json([
            'code' => 1,
            'msg' => 'ok',
            'data' => $member?->only(['id', 'username', 'nickname', 'avatar', 'email', 'mobile']),
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $member = $this->member($request);
        if (! $member) {
            return response()->json(['code' => 0, 'msg' => '未登录'], 401);
        }

        $data = $request->validate([
            'old_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (! $member->verifyPassword($data['old_password'])) {
            return response()->json(['code' => 0, 'msg' => '原密码不正确']);
        }

        $salt = bin2hex(random_bytes(8));
        $member->password = Member::encryptPassword($data['password'], $salt);
        $member->salt = $salt;
        $member->save();

        return response()->json(['code' => 1, 'msg' => '密码已更新']);
    }

    /**
     * 无邮件渠道：凭用户名 + 已绑定手机号重置密码（生产环境请加短信验证码）。
     */
    public function resetPassword(Request $request): JsonResponse
    {
        if (! config('cms.api.allow_password_reset', false)) {
            return response()->json(['code' => 0, 'msg' => '密码重置未开放，请联系管理员'], 403);
        }

        $data = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'mobile' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $member = Member::query()->where('username', $data['username'])->first();
        if (! $member || $member->status !== 'normal') {
            return response()->json(['code' => 0, 'msg' => '用户不存在或已禁用']);
        }
        if ($member->mobile === '' || $member->mobile !== $data['mobile']) {
            return response()->json(['code' => 0, 'msg' => '手机号与账号不匹配']);
        }

        $salt = bin2hex(random_bytes(8));
        $member->password = Member::encryptPassword($data['password'], $salt);
        $member->salt = $salt;
        $member->token = '';
        $member->save();

        return response()->json(['code' => 1, 'msg' => '密码已重置，请重新登录']);
    }
}
