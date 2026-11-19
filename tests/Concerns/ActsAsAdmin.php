<?php

namespace Tests\Concerns;

use App\Models\Admin\Admin;

trait ActsAsAdmin
{
    protected function loginAsAdmin(?Admin $admin = null): Admin
    {
        $admin ??= Admin::query()->where('username', 'admin')->firstOrFail();

        $sessionKey = config('cms.session_key', 'cms_admin');
        $this->withSession([
            $sessionKey => [
                'id' => $admin->id,
                'username' => $admin->username,
                'nickname' => $admin->nickname,
                'avatar' => $admin->avatar,
                'token' => $admin->token ?: '',
            ],
        ]);

        return $admin;
    }
}
