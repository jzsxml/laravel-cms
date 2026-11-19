<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>管理员登录 — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
</head>
<body class="cms-login">
<div class="cms-login-card card">
    <header class="cms-login-header">
        <i class="fas fa-shield-halved fa-2x opacity-75" aria-hidden="true"></i>
        <h1>{{ config('app.name') }}</h1>
        <p class="small mb-0 opacity-75">管理后台</p>
    </header>
    <section class="cms-login-body">
        @if(session('error'))
            <div class="alert alert-danger py-2 small" role="alert">{{ session('error') }}</div>
        @endif
        <form method="post" action="{{ route('admin.login.post') }}">
            @csrf
            <fieldset class="mb-3">
                <legend class="form-label text-secondary small fw-semibold">用户名</legend>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted" aria-hidden="true"></i></span>
                    <input type="text" name="username" value="{{ old('username') }}" class="form-control border-start-0 ps-0" required autofocus placeholder="请输入用户名">
                </div>
            </fieldset>
            <fieldset class="mb-4">
                <legend class="form-label text-secondary small fw-semibold">密码</legend>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted" aria-hidden="true"></i></span>
                    <input type="password" name="password" class="form-control border-start-0 ps-0" required placeholder="请输入密码">
                </div>
            </fieldset>
            <button type="submit" class="btn btn-primary w-100 btn-login">
                <i class="fas fa-right-to-bracket me-1" aria-hidden="true"></i> 登录
            </button>
        </form>
        <p class="text-muted small text-center mt-4 mb-0">默认账号 admin / 123456</p>
    </section>
</div>
</body>
</html>