<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '会员中心') — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/member.css') }}" rel="stylesheet">
</head>
<body class="cms-member">
<nav class="navbar navbar-dark cms-member-nav mb-4">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="{{ route('member.home') }}">
            <i class="fas fa-user-circle me-2"></i>会员中心
        </a>
        @php($memberNav = app(\App\Services\Member\MemberAuth::class)->user())
        <div class="d-flex align-items-center gap-3 text-white small">
            <span>{{ $memberNav['nickname'] ?? $memberNav['username'] ?? '' }}</span>
            <a href="{{ route('member.profile') }}" class="link-light text-decoration-none">资料</a>
            <a href="{{ route('member.contribute') }}" class="link-light text-decoration-none">投稿</a>
            <a href="{{ route('member.wallet') }}" class="link-light text-decoration-none">钱包</a>
            <form method="post" action="{{ route('member.logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light">退出</button>
            </form>
        </div>
    </div>
</nav>
<main class="container pb-5">
    @if(session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger py-2">{{ session('error') }}</div>
    @endif
    @yield('content')
</main>
</body>
</html>