<?php

namespace App\Http\Controllers\Admin\General;

use App\Http\Controllers\Admin\Controller;
use App\Models\Admin\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(Request $request): View|JsonResponse|RedirectResponse
    {
        $admin = $this->adminAuth->adminModel();
        if (! $admin) {
            return redirect()->route('admin.login');
        }

        if ($request->isMethod('post')) {
            $data = $request->validate([
                'nickname' => ['required', 'string', 'max:50'],
                'email' => ['nullable', 'email', 'max:100'],
                'mobile' => ['nullable', 'string', 'max:20'],
                'avatar' => ['nullable', 'string', 'max:255'],
                'password' => ['nullable', 'string', 'min:6', 'max:32'],
                'password_confirm' => ['nullable', 'same:password'],
            ]);

            $admin->nickname = $data['nickname'];
            $admin->email = $data['email'] ?? '';
            $admin->mobile = $data['mobile'] ?? '';
            if (! empty($data['avatar'])) {
                $admin->avatar = $data['avatar'];
            }
            if (! empty($data['password'])) {
                $salt = bin2hex(random_bytes(8));
                $admin->salt = $salt;
                $admin->password = Admin::encryptPassword($data['password'], $salt);
            }
            $admin->save();

            $session = $this->adminAuth->user();
            if ($session) {
                $session['nickname'] = $admin->nickname;
                $session['avatar'] = $admin->avatar;
                session()->put(config('cms.session_key', 'cms_admin'), $session);
            }

            $this->adminAuth->recordLog($request, '个人资料', '更新资料');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['code' => 1, 'msg' => '保存成功']);
            }

            return redirect()->route('admin.profile')->with('success', '保存成功');
        }

        return view('admin.general.profile.index', [
            'title' => '个人资料',
            'admin' => $admin,
            'uploadUrl' => route('admin.ajax.upload'),
        ]);
    }
}
