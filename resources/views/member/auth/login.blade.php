<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>会员登录 — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/member.css') }}" rel="stylesheet">
</head>
<body class="cms-member-login">
<div class="card cms-member-login-card">
    <div class="card-body p-4">
        <h1 class="h4 text-center mb-1">会员登录</h1>
        <p class="text-muted small text-center mb-4">与 API 共用会员账号</p>
        @if(session('error'))
            <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
        @endif
        <form method="post" action="{{ route('member.login.post') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label small">用户名</label>
                <input type="text" name="username" value="{{ old('username') }}" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label small">密码</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">登录</button>
        </form>
        <p class="small text-center mt-3 mb-0">
            没有账号？<a href="{{ route('member.register') }}">注册</a>
            · <a href="{{ url('/') }}">首页</a>
        </p>
    </div>
</div>
</body>
</html>