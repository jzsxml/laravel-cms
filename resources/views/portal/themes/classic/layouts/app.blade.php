<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $site['sitename'] ?? config('app.name'))</title>
    @stack('meta')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/portal.css') }}" rel="stylesheet">
    <link href="{{ asset('css/portal-classic.css') }}" rel="stylesheet">
</head>
<body class="{{ $portalBodyClass ?? 'cms-portal-site cms-portal-theme-classic' }} d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-dark portal-nav">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="{{ route('portal.home') }}">{{ $site['sitename'] ?: config('app.name') }} <span class="badge text-bg-light text-dark ms-1 small">Classic</span></a>
        <div class="collapse navbar-collapse show">
            <ul class="navbar-nav ms-auto gap-lg-1">
                <li class="nav-item"><a class="nav-link" href="{{ route('portal.home') }}">首页</a></li>
                @foreach($nav ?? [] as $item)
                    <li class="nav-item"><a class="nav-link" href="{{ route('portal.category', $item->nickname) }}">{{ $item->name }}</a></li>
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
    <div class="container small text-center opacity-75">Classic 主题 · {{ $site['sitename'] ?: config('app.name') }}</div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>