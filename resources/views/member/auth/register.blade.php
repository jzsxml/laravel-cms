<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>会员注册 — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/member.css') }}" rel="stylesheet">
</head>
<body class="cms-member-login">
<div class="card cms-member-login-card">
    <div class="card-body p-4">
        <h1 class="h4 text-center mb-4">注册会员</h1>
        @if(session('error'))
            <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
        @endif
        <form method="post" action="{{ route('member.register.post') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label small">用户名</label>
                <input type="text" name="username" value="{{ old('username') }}" class="form-control" required pattern="[A-Za-z0-9_-]+">
            </div>
            <div class="mb-3">
                <label class="form-label small">密码</label>
                <input type="password" name="password" class="form-control" required minlength="6">
            </div>
            <div class="mb-3">
                <label class="form-label small">确认密码</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label small">邮箱（可选）</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label small">手机（可选）</label>
                <input type="text" name="mobile" value="{{ old('mobile') }}" class="form-control">
            </div>
            <button type="submit" class="btn btn-success w-100">注册</button>
        </form>
        <p class="small text-center mt-3 mb-0"><a href="{{ route('member.login') }}">已有账号，去登录</a></p>
    </div>
</div>
</body>
</html>