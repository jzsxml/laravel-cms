<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '后台') — {{ config('app.name') }}</title>
    <script>
        (function () {
            var theme = localStorage.getItem('cms_admin_theme');
            if (theme) document.documentElement.setAttribute('data-cms-theme', theme);
            if (localStorage.getItem('cms_sidebar_collapsed') === '1') {
                document.documentElement.classList.add('cms-sidebar-is-collapsed');
            }
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="cms-admin">
<div class="d-flex cms-shell" id="cms-shell">
    <aside class="cms-sidebar" id="cms-sidebar">
        <div class="cms-brand">
            <button type="button" class="cms-brand-toggle btn btn-link p-0 text-white" id="btn-sidebar" title="折叠/展开菜单" aria-label="折叠菜单">
                <i class="fas fa-bars"></i>
            </button>
            <i class="fas fa-cubes cms-brand-icon"></i>
            <span class="cms-brand-text">{{ config('app.name') }}</span>
        </div>
        <div class="cms-menu-search px-3 pb-2">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="fas fa-search"></i></span>
                <input type="search" class="form-control border-secondary-subtle" id="sidebar-menu-search" placeholder="{{ __('admin.ui.menu_search') }}" autocomplete="off">
            </div>
        </div>
        <nav class="cms-menu" id="cms-menu">
            @foreach($menu ?? [] as $item)
                @php
                    $childTitles = collect($item['children'] ?? [])->pluck('title')->implode(' ');
                    $searchBlob = mb_strtolower($item['title'].' '.$childTitles);
                    $href = $item['name'] === 'index/index'
                        ? route('admin.dashboard')
                        : '#';
                    $isParentActive = $item['name'] === 'index/index'
                        ? request()->routeIs('admin.dashboard')
                        : false;
                    $hasChildren = ! empty($item['children']);
                    $subOpen = $hasChildren && collect($item['children'])->contains(function ($child) {
                        $ruleName = $child['name'];
                        if (str_ends_with($ruleName, '/index')) {
                            $path = substr($ruleName, 0, -strlen('/index'));
                        } else {
                            $path = preg_replace('#/[a-z]+$#', '', $ruleName) ?: $ruleName;
                        }
                        $adminPrefix = config('cms.admin_path');
                        return request()->is($adminPrefix.'/'.$path) || request()->is($adminPrefix.'/'.$path.'/*');
                    });
                @endphp
                <div class="cms-menu-group" data-menu-search="{{ $searchBlob }}">
                    <a href="{{ $href }}"
                       class="cms-menu-top {{ $isParentActive ? 'active' : '' }} {{ $hasChildren ? 'has-children' : '' }}"
                       @if($hasChildren) data-menu-toggle="sub" @endif
                       title="{{ $item['title'] }}">
                        <i class="{{ $item['icon'] ?: 'fa fa-circle' }}"></i>
                        <span class="cms-menu-label">{{ $item['title'] }}</span>
                        @if($hasChildren)
                            <i class="fas fa-chevron-down cms-menu-chevron ms-auto {{ $subOpen ? 'is-open' : '' }}"></i>
                        @endif
                    </a>
                    @if($hasChildren)
                        <div class="sub cms-submenu {{ $subOpen ? '' : 'is-collapsed' }}">
                            @foreach($item['children'] as $child)
                                @php
                                    $ruleName = $child['name'];
                                    if (str_ends_with($ruleName, '/index')) {
                                        $path = substr($ruleName, 0, -strlen('/index'));
                                    } else {
                                        $path = preg_replace('#/[a-z]+$#', '', $ruleName) ?: $ruleName;
                                    }
                                    $childHref = url(config('cms.admin_path').'/'.$path);
                                    $adminPrefix = config('cms.admin_path');
                                    $menuActive = request()->is($adminPrefix.'/'.$path)
                                        || request()->is($adminPrefix.'/'.$path.'/*');
                                @endphp
                                <a href="{{ $childHref }}"
                                   class="{{ $menuActive ? 'active' : '' }}"
                                   title="{{ $child['title'] }}"
                                   data-menu-search="{{ mb_strtolower($child['title']) }}">
                                    {{ $child['title'] }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
            <p class="cms-menu-empty text-center text-muted small px-3 d-none" id="menu-empty">无匹配菜单</p>
        </nav>
    </aside>
    <div class="cms-sidebar-backdrop" id="cms-sidebar-backdrop" hidden></div>
    <div class="cms-main">
        <header class="cms-topbar">
            <div class="cms-topbar-row d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                    <button type="button" class="btn btn-sm btn-link text-white d-md-none cms-mobile-menu" id="btn-sidebar-mobile" aria-label="打开菜单">
                        <i class="fas fa-bars fa-lg"></i>
                    </button>
                    <h1 class="cms-page-title mb-0 text-truncate">@yield('title', '控制台')</h1>
                    <div class="cms-topbar-search flex-grow-1 d-none d-lg-block" style="max-width:320px">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white bg-opacity-25 border-0 text-white"><i class="fas fa-search"></i></span>
                            <input type="search" class="form-control border-0" id="topbar-menu-search" placeholder="搜索菜单并跳转…" autocomplete="off">
                        </div>
                        <div class="cms-search-dropdown d-none" id="topbar-search-results" role="listbox"></div>
                    </div>
                </div>
                <div class="cms-topbar-tools d-flex align-items-center gap-1">
                    <div class="dropdown">
                        <button type="button" class="btn btn-sm btn-link text-white" data-bs-toggle="dropdown" title="{{ __('admin.ui.lang') }}">
                            <i class="fas fa-language"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><h6 class="dropdown-header">{{ __('admin.ui.lang') }}</h6></li>
                            <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['lang' => 'zh_CN']) }}">{{ __('admin.ui.lang_zh') }}</a></li>
                            <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}">{{ __('admin.ui.lang_en') }}</a></li>
                        </ul>
                    </div>
                    <div class="dropdown">
                        <button type="button" class="btn btn-sm btn-link text-white" data-bs-toggle="dropdown" title="更换主题" aria-label="更换主题">
                            <i class="fas fa-palette"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow cms-theme-picker">
                            <li><h6 class="dropdown-header">界面主题</h6></li>
                            <li><button type="button" class="dropdown-item" data-cms-theme="default"><span class="cms-theme-dot" style="background:#3c8dbc"></span> 默认蓝</button></li>
                            <li><button type="button" class="dropdown-item" data-cms-theme="dark"><span class="cms-theme-dot" style="background:#374151"></span> 深色</button></li>
                            <li><button type="button" class="dropdown-item" data-cms-theme="green"><span class="cms-theme-dot" style="background:#00a65a"></span> 清新绿</button></li>
                            <li><button type="button" class="dropdown-item" data-cms-theme="purple"><span class="cms-theme-dot" style="background:#605ca8"></span> 典雅紫</button></li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-sm btn-link text-white" id="btn-fullscreen" title="全屏" aria-label="全屏">
                        <i class="fas fa-expand" id="fullscreen-icon"></i>
                    </button>
                    <a href="{{ route('admin.profile') }}" class="cms-user-name text-decoration-none text-white d-none d-sm-inline">
                        <i class="fas fa-user-circle me-1"></i>
                        {{ $admin['nickname'] ?? $admin['username'] ?? '' }}
                    </a>
                    <form action="{{ route('admin.logout') }}" method="post" class="d-inline mb-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-light">{{ __('admin.ui.logout') }}</button>
                    </form>
                </div>
            </div>
        </header>
        <main class="cms-content">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
@php
    $cmsMenuLinks = [];
    foreach ($menu ?? [] as $item) {
        if (($item['name'] ?? '') === 'index/index') {
            $cmsMenuLinks[] = ['title' => $item['title'], 'url' => route('admin.dashboard')];
        }
        foreach ($item['children'] ?? [] as $child) {
            $ruleName = $child['name'];
            if (str_ends_with($ruleName, '/index')) {
                $path = substr($ruleName, 0, -strlen('/index'));
            } else {
                $path = preg_replace('#/[a-z]+$#', '', $ruleName) ?: $ruleName;
            }
            $cmsMenuLinks[] = ['title' => $child['title'], 'url' => url(config('cms.admin_path').'/'.$path)];
        }
    }
@endphp
<script>window.__cmsAdminMenu = @json($cmsMenuLinks);</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/admin-shell.js') }}"></script>
@stack('scripts')
</body>
</html>