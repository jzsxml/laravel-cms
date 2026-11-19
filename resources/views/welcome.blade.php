<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/member.css') }}" rel="stylesheet">
</head>
<body class="cms-portal">
<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="fw-semibold">{{ config('app.name') }}</h1>
        <p class="text-muted">Laravel CMS — 管理后台与会员中心</p>
    </div>
    <div class="row g-4 justify-content-center">
        <div class="col-md-5">
            <a href="{{ url($adminPath) }}" class="card cms-portal-card text-decoration-none h-100">
                <div class="card-body text-center p-4">
                    <i class="fas fa-shield-halved fa-3x text-primary mb-3"></i>
                    <h2 class="h5 text-dark">管理后台</h2>
                    <p class="text-muted small mb-0">/ {{ $adminPath }}</p>
                </div>
            </a>
        </div>
        <div class="col-md-5">
            <a href="{{ route('member.login') }}" class="card cms-portal-card text-decoration-none h-100">
                <div class="card-body text-center p-4">
                    <i class="fas fa-user-circle fa-3x text-success mb-3"></i>
                    <h2 class="h5 text-dark">会员中心</h2>
                    <p class="text-muted small mb-0">/ {{ $memberPath }}</p>
                </div>
            </a>
        </div>
    </div>
</div>
</body>
</html>