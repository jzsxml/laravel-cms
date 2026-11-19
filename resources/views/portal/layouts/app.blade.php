<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $site['sitename'] ?? config('app.name'))</title>
    @stack('meta')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">
    @foreach(\App\Support\PortalTheme::stylesheets() as $href)
        <link href="{{ $href }}" rel="stylesheet">
    @endforeach
</head>
<body class="{{ $portalBodyClass ?? 'cms-portal-site' }} d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-dark portal-nav">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="{{ route('portal.home') }}">
            @php($logoUrl = \App\Support\SafeUrl::forDisplay($site['site_logo'] ?? ''))
            @if($logoUrl !== '')
                <img src="{{ $logoUrl }}" alt="" height="32" class="me-2">
            @endif
            {{ $site['sitename'] ?: config('app.name') }}
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#portalNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <form class="d-flex order-lg-3 ms-lg-2 mt-2 mt-lg-0" action="{{ route('portal.search') }}" method="get" role="search" aria-label="站内搜索">
            <input class="form-control form-control-sm" type="search" name="q" value="{{ request('q') }}" placeholder="搜索" style="min-width:120px">
        </form>
        <div class="collapse navbar-collapse order-lg-2" id="portalNav">
            <ul class="navbar-nav ms-lg-auto gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('portal.home') ? 'active' : '' }}" href="{{ route('portal.home') }}">首页</a>
                </li>
                @foreach($nav ?? [] as $item)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('c/'.$item->nickname) || request()->is('c/'.$item->nickname.'/*') ? 'active' : '' }}"
                           href="{{ route('portal.category', $item->nickname) }}">{{ $item->name }}</a>
                    </li>
                @endforeach
                @foreach($navPages ?? [] as $p)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('p/'.$p->slug) ? 'active' : '' }}" href="{{ route('portal.page', $p->slug) }}">{{ $p->title }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</nav>
<main class="flex-grow-1 py-4">
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success py-2 small">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
        @endif
        @yield('content')
    </div>
</main>
<footer class="portal-footer py-4 mt-auto">
    <div class="container small">
        <div class="d-flex flex-wrap justify-content-between gap-3">
            <div>
                @if(!empty($site['site_contact_phone']))<div><i class="fas fa-phone me-2"></i>{{ $site['site_contact_phone'] }}</div>@endif
                @if(!empty($site['site_contact_email']))<div><i class="fas fa-envelope me-2"></i>{{ $site['site_contact_email'] }}</div>@endif
            </div>
            <div class="text-end">
                <a href="{{ url(config('cms.admin_path')) }}" class="link-light link-opacity-75 me-3">管理后台</a>
                <a href="{{ route('member.login') }}" class="link-light link-opacity-75 me-3">会员中心</a>
                <a href="{{ route('portal.page', 'lian-xi-wo-men') }}" class="link-light link-opacity-75">联系我们</a>
                @if(!empty($site['site_icp']))
                    <div class="mt-2 opacity-75">{{ $site['site_icp'] }}</div>
                @endif
            </div>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>