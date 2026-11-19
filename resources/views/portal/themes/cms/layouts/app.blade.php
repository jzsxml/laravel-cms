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
    @stack('styles')
</head>
<body class="{{ $portalBodyClass ?? 'cms-portal-site cms-portal-theme-cms' }} d-flex flex-column min-vh-100">
<header class="cms-topbar sticky-top">
    <nav class="navbar navbar-expand-lg navbar-light py-0">
        <div class="container">
            <a class="navbar-brand py-2" href="{{ route('portal.home') }}">
                @php($logoUrl = \App\Support\SafeUrl::forDisplay($site['site_logo'] ?? ''))
                @if($logoUrl !== '')
                    <img src="{{ $logoUrl }}" alt="{{ $site['sitename'] }}">
                @else
                    <span class="fw-semibold fs-5 text-dark">{{ $site['sitename'] ?: '我的CMS网站' }}</span>
                @endif
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#cmsNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="cmsNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('portal.home') ? 'active' : '' }}" href="{{ route('portal.home') }}">首页</a>
                    </li>
                    @foreach($navTree ?? [] as $node)
                        @php $parent = $node['category']; $children = $node['children'] ?? []; @endphp
                        @if($children !== [])
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="{{ route('portal.category', $parent->nickname) }}" data-bs-toggle="dropdown">{{ $parent->name }}</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('portal.category', $parent->nickname) }}">全部{{ $parent->name }}</a></li>
                                    @foreach($children as $childNode)
                                        @php $child = $childNode['category']; @endphp
                                        <li><a class="dropdown-item" href="{{ route('portal.category', $child->nickname) }}">{{ $child->name }}</a></li>
                                    @endforeach
                                </ul>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('c/'.$parent->nickname) || request()->is('c/'.$parent->nickname.'/*') ? 'active' : '' }}" href="{{ route('portal.category', $parent->nickname) }}">{{ $parent->name }}</a>
                            </li>
                        @endif
                    @endforeach
                    @foreach($navPages ?? [] as $p)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('portal.page', $p->slug) }}">{{ $p->title }}</a>
                        </li>
                    @endforeach
                    @if(($firstSpecial ?? null))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('portal.special', $firstSpecial->slug) }}">专题</a>
                        </li>
                    @endif
                </ul>
                <form class="d-flex" action="{{ route('portal.search') }}" method="get" role="search">
                    <input class="form-control form-control-sm" type="search" name="q" placeholder="搜索" value="{{ request('q') }}">
                </form>
            </div>
        </div>
    </nav>
</header>

<main class="flex-grow-1 py-3">
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

<footer class="cms-footer mt-auto">
    <div class="container">
        <div class="row g-3 small">
            <div class="col-md-4">
                <strong class="text-white d-block mb-2">{{ $site['sitename'] ?: config('app.name') }}</strong>
                <p class="mb-0">{{ $site['site_slogan'] ?: '内容管理系统门户演示' }}</p>
            </div>
            <div class="col-md-4">
                @if(!empty($site['site_contact_phone']))<div><i class="fas fa-phone me-2"></i>{{ $site['site_contact_phone'] }}</div>@endif
                @if(!empty($site['site_contact_email']))<div><i class="fas fa-envelope me-2"></i>{{ $site['site_contact_email'] }}</div>@endif
            </div>
            <div class="col-md-4 text-md-end">
                <a href="{{ route('portal.page', 'lian-xi-wo-men') }}" class="me-3">关于我们</a>
                <a href="{{ url(config('cms.admin_path')) }}" class="me-3">管理后台</a>
                <a href="{{ route('member.login') }}">会员中心</a>
                @if(!empty($site['site_icp']))
                    <div class="mt-2"><a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener">{{ $site['site_icp'] }}</a></div>
                @endif
            </div>
        </div>
        <div class="text-center mt-3 pt-3 border-top border-secondary border-opacity-25 small opacity-75">
            © {{ date('Y') }} {{ $site['sitename'] ?: config('app.name') }}
        </div>
    </div>
</footer>

<a href="#" class="cms-back-top btn btn-primary btn-sm rounded-circle" id="cmsBackTop" title="返回顶部" aria-label="返回顶部"><i class="fas fa-arrow-up"></i></a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('cmsBackTop')?.addEventListener('click', (e) => {
    e.preventDefault();
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
</script>
@stack('scripts')
</body>
</html>