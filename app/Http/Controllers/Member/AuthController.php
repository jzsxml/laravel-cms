<?php

namespace App\Http\Controllers\Member;

use App\Models\Api\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function loginForm(Request $request): View|RedirectResponse
    {
        if ($this->memberAuth->check()) {
            return redirect()->route('member.home');
        }

        return view('member.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ]);

        $member = Member::query()->where('username', $data['username'])->first();
        if (! $member || $member->status !== 'normal' || ! $member->verifyPassword($data['password'])) {
            return back()->withInput()->with('error', '用户名或密码错误');
        }

        $this->memberAuth->login($member, $request);

        return redirect()->intended(route('member.home'));
    }

    public function registerForm(Request $request): View|RedirectResponse
    {
        if ($this->memberAuth->check()) {
            return redirect()->route('member.home');
        }

        return view('member.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:50', 'alpha_dash'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'email' => ['nullable', 'email'],
            'mobile' => ['nullable', 'string', 'max:20'],
        ]);

        if (Member::query()->where('username', $data['username'])->exists()) {
            return back()->withInput()->with('error', '用户名已存在');
        }

        $salt = bin2hex(random_bytes(8));
        $member = Member::query()->create([
            'username' => $data['username'],
            'nickname' => $data['username'],
            'password' => Member::encryptPassword($data['password'], $salt),
            'salt' => $salt,
            'email' => $data['email'] ?? '',
            'mobile' => $data['mobile'] ?? '',
            'status' => 'normal',
        ]);

        $this->memberAuth->login($member, $request);

        return redirect()->route('member.home')->with('success', '注册成功');
    }

    public function logout(Request $request): RedirectResponse
    {
        $member = $this->memberAuth->member();
        if ($member) {
            $member->token = '';
            $member->save();
        }
        $this->memberAuth->logout();

        return redirect()->route('member.login');
    }
}
