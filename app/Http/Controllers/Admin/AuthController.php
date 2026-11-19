<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Admin;
use App\Services\Admin\AdminAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(protected AdminAuth $adminAuth) {}

    public function loginForm(): View|RedirectResponse
    {
        if ($this->adminAuth->user()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ]);

        $admin = Admin::query()->where('username', $credentials['username'])->first();
        if (! $admin || $admin->status !== 'normal' || ! $admin->verifyPassword($credentials['password'])) {
            if ($admin) {
                $admin->loginfailure++;
                $admin->save();
            }

            return back()
                ->withInput($request->only('username'))
                ->with('error', '用户名或密码错误');
        }

        $this->adminAuth->login($admin, $request);
        $this->adminAuth->recordLog($request, '登录', '管理员登录成功');

        return redirect()->intended(route('admin.dashboard'));
    }
}
