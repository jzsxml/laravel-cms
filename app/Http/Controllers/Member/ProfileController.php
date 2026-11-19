<?php

namespace App\Http\Controllers\Member;

use App\Models\Api\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $member = $this->memberAuth->member();
        if (! $member) {
            return redirect()->route('member.login');
        }

        if ($request->isMethod('post')) {
            if ($request->input('form') === 'password') {
                return $this->savePassword($request, $member);
            }

            return $this->saveProfile($request, $member);
        }

        return view('member.profile.index', ['member' => $member]);
    }

    protected function saveProfile(Request $request, Member $member): RedirectResponse
    {
        $data = $request->validate([
            'nickname' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'string', 'max:255'],
        ]);

        $member->fill($data);
        $member->save();

        return back()->with('success', '资料已保存');
    }

    protected function savePassword(Request $request, Member $member): RedirectResponse
    {
        $data = $request->validate([
            'old_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (! $member->verifyPassword($data['old_password'])) {
            return back()->with('error', '原密码不正确');
        }

        $salt = bin2hex(random_bytes(8));
        $member->password = Member::encryptPassword($data['password'], $salt);
        $member->salt = $salt;
        $member->save();

        return back()->with('success', '密码已更新');
    }
}
