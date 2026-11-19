@extends('admin.layouts.app')

@section('title', $title)

@section('content')
<div class="card cms-card">
    <div class="card-body p-4">
        <form id="admin-form" method="post" action="{{ $action }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">用户名 <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control" required maxlength="50" pattern="[a-zA-Z0-9_]+"
                           value="{{ old('username', $admin->username) }}" @readonly($admin->exists && (int) $admin->id === 1)>
                </div>
                <div class="col-md-6">
                    <label class="form-label">昵称 <span class="text-danger">*</span></label>
                    <input type="text" name="nickname" class="form-control" required maxlength="50"
                           value="{{ old('nickname', $admin->nickname) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">邮箱</label>
                    <input type="email" name="email" class="form-control" maxlength="100" value="{{ old('email', $admin->email) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">手机</label>
                    <input type="text" name="mobile" class="form-control" maxlength="20" value="{{ old('mobile', $admin->mobile) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">状态</label>
                    <select name="status" class="form-select" @disabled($admin->exists && (int) $admin->id === 1)>
                        <option value="normal" @selected(old('status', $admin->status) === 'normal')>正常</option>
                        <option value="hidden" @selected(old('status', $admin->status) === 'hidden')>隐藏</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">角色组 <span class="text-danger">*</span>（可多选）</label>
                    <div class="d-flex flex-wrap gap-3 border rounded p-3 bg-light">
                        @foreach($groups as $group)
                            <label class="form-check">
                                <input type="checkbox" class="form-check-input" name="group_ids[]" value="{{ $group->id }}"
                                       @checked(in_array($group->id, old('group_ids', $selectedGroupIds), true))>
                                <span class="form-check-label">{{ $group->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">密码 @if(!$admin->exists)<span class="text-danger">*</span>@endif</label>
                    <input type="password" name="password" class="form-control" minlength="6" maxlength="32" autocomplete="new-password"
                           @if(!$admin->exists) required @endif>
                    @if($admin->exists)<div class="form-text">留空不修改</div>@endif
                </div>
                <div class="col-md-6">
                    <label class="form-label">确认密码</label>
                    <input type="password" name="password_confirm" class="form-control" minlength="6" maxlength="32" autocomplete="new-password">
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">保存</button>
                <a href="{{ $backUrl }}" class="btn btn-secondary">返回</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('admin-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const res = await fetch(e.target.action, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: new FormData(e.target),
    });
    const data = await res.json();
    if (data.errors) {
        alert(Object.values(data.errors).flat().join('\n'));
        return;
    }
    alert(data.msg || '完成');
    if (data.code === 1) window.location.href = @json($backUrl);
});
</script>
@endpush