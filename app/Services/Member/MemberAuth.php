<?php

namespace App\Services\Member;

use App\Models\Api\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class MemberAuth
{
    protected string $sessionKey;

    public function __construct()
    {
        $this->sessionKey = (string) config('cms.member_session_key', 'cms_member');
    }

    public function login(Member $member, Request $request): void
    {
        $member->token = bin2hex(random_bytes(16));
        $member->logintime = time();
        $member->loginip = $request->ip() ?? '';
        $member->save();

        Session::put($this->sessionKey, [
            'id' => $member->id,
            'username' => $member->username,
            'nickname' => $member->nickname,
            'avatar' => $member->avatar,
            'token' => $member->token,
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

    public function memberId(): ?int
    {
        $user = $this->user();

        return isset($user['id']) ? (int) $user['id'] : null;
    }

    public function member(): ?Member
    {
        $id = $this->memberId();
        if (! $id) {
            return null;
        }

        return Member::query()->whereKey($id)->where('status', 'normal')->first();
    }

    public function check(): bool
    {
        return $this->member() !== null;
    }
}
