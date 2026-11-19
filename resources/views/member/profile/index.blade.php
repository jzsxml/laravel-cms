@extends('member.layouts.app')

@section('title', '个人资料')

@section('content')
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card cms-member-card">
            <div class="card-header bg-white fw-semibold">基本资料</div>
            <div class="card-body">
                <form method="post" action="{{ route('member.profile') }}">
                    @csrf
                    <input type="hidden" name="form" value="profile">
                    <div class="mb-3">
                        <label class="form-label small">昵称</label>
                        <input type="text" name="nickname" class="form-control" value="{{ old('nickname', $member->nickname) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">邮箱</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $member->email) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">手机</label>
                        <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $member->mobile) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">头像 URL</label>
                        <input type="text" name="avatar" class="form-control" value="{{ old('avatar', $member->avatar) }}">
                    </div>
                    <button type="submit" class="btn btn-success">保存</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card cms-member-card">
            <div class="card-header bg-white fw-semibold">修改密码</div>
            <div class="card-body">
                <form method="post" action="{{ route('member.profile') }}">
                    @csrf
                    <input type="hidden" name="form" value="password">
                    <div class="mb-3">
                        <label class="form-label small">原密码</label>
                        <input type="password" name="old_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">新密码</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">确认新密码</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-outline-success">更新密码</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection