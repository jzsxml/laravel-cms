@extends('admin.layouts.app')

@section('title', $title)

@section('content')
<div class="card cms-card">
    <div class="card-body p-4" style="max-width:520px">
        <form method="post" action="{{ route('admin.profile') }}" id="profile-form">
            @csrf
            <div class="mb-3">
                <label class="form-label text-muted">用户名</label>
                <input type="text" class="form-control" value="{{ $admin->username }}" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">昵称 <span class="text-danger">*</span></label>
                <input type="text" name="nickname" class="form-control" required maxlength="50"
                       value="{{ old('nickname', $admin->nickname) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">邮箱</label>
                <input type="email" name="email" class="form-control" maxlength="100"
                       value="{{ old('email', $admin->email) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">手机</label>
                <input type="text" name="mobile" class="form-control" maxlength="20"
                       value="{{ old('mobile', $admin->mobile) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">头像 URL</label>
                <div class="d-flex gap-2 align-items-start flex-wrap">
                    <input type="text" name="avatar" class="form-control" id="avatar" maxlength="255"
                           value="{{ old('avatar', $admin->avatar) }}">
                    <input type="file" class="form-control form-control-sm" style="max-width:200px" id="avatar-file">
                </div>
                @if($admin->avatar)
                    <img src="{{ $admin->avatar }}" alt="" class="mt-2 rounded" style="max-height:64px">
                @endif
            </div>
            <hr>
            <p class="small text-muted mb-2">留空则不修改密码</p>
            <div class="mb-3">
                <label class="form-label">新密码</label>
                <input type="password" name="password" class="form-control" minlength="6" maxlength="32" autocomplete="new-password">
            </div>
            <div class="mb-3">
                <label class="form-label">确认密码</label>
                <input type="password" name="password_confirm" class="form-control" minlength="6" maxlength="32" autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('avatar-file')?.addEventListener('change', async (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    const fd = new FormData();
    fd.append('file', file);
    fd.append('_token', document.querySelector('meta[name=csrf-token]').content);
    const res = await fetch(@json($uploadUrl), {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: fd,
    });
    const data = await res.json();
    if (data.code === 1 && data.data?.url) {
        document.getElementById('avatar').value = data.data.url;
    } else {
        alert(data.msg || '上传失败');
    }
});
</script>
@endpush