@extends($portalLayout ?? 'portal.layouts.app')

@section('title', ($site['sitename'] ?: config('app.name')).' — 首页')

@section('content')
@php
    $heroStyle = !empty($site['site_hero_image'])
        ? "background-image:linear-gradient(rgba(30,58,95,.75),rgba(30,58,95,.85)),url('{$site['site_hero_image']}');background-size:cover"
        : '';
@endphp
<div class="portal-hero p-4 p-md-5 mb-4 d-flex align-items-center"{!! $heroStyle !== '' ? ' style="'.e($heroStyle).'"' : '' !!}>
    <div>
        <h1 class="display-6 fw-semibold mb-2">{{ $site['sitename'] ?: config('app.name') }}</h1>
        <p class="lead mb-0 opacity-90">{{ $site['site_slogan'] ?: '企业门户网站' }}</p>
    </div>
</div>

@php
    $homeBanner = \App\Support\CmsBlock::html('home_banner');
@endphp
@if($homeBanner !== '')
<section class="mb-4">{!! $homeBanner !!}</section>
@endif

@if(($top ?? collect())->isNotEmpty())
<section class="mb-4">
    <h2 class="h5 fw-semibold mb-3">置顶</h2>
    <ul class="list-group portal-card">
        @foreach($top as $item)
            @php $cat = $item->category; @endphp
            @if($cat && $item->slug)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <a href="{{ route('portal.archive', [$cat->nickname, $item->slug]) }}">{{ $item->title }}</a>
                <span class="badge bg-warning text-dark">置顶</span>
            </li>
            @endif
        @endforeach
    </ul>
</section>
@endif

@if(($recommend ?? collect())->isNotEmpty())
<section class="mb-5">
    <h2 class="h5 fw-semibold mb-3">推荐内容</h2>
    <div class="row g-3">
        @foreach($recommend as $item)
            @php $cat = $item->category; @endphp
            @if($cat && $item->slug)
            <div class="col-md-4">
                <a href="{{ route('portal.archive', [$cat->nickname, $item->slug]) }}" class="card portal-card h-100 text-decoration-none text-dark">
                    @if($item->image)
                        <img src="{{ $item->image }}" class="card-img-top" alt="" style="height:140px;object-fit:cover">
                    @endif
                    <div class="card-body">
                        <h3 class="h6 card-title">{{ $item->title }}</h3>
                        <p class="card-text small text-muted mb-0">{{ \Illuminate\Support\Str::limit($item->description, 80) }}</p>
                    </div>
                </a>
            </div>
            @endif
        @endforeach
    </div>
</section>
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <h2 class="h5 fw-semibold mb-3">新闻动态</h2>
        <ul class="list-group list-group-flush portal-card">
            @forelse($latestNews as $item)
                @php $newsCat = $item->category ?? \App\Models\Admin\Category::find($item->category_id); @endphp
                <li class="list-group-item bg-white">
                    @if($newsCat && $item->slug)
                        <a href="{{ route('portal.archive', [$newsCat->nickname, $item->slug]) }}" class="text-decoration-none">{{ $item->title }}</a>
                    @else
                        {{ $item->title }}
                    @endif
                    <span class="text-muted small ms-2">{{ $item->publishtime ? date('Y-m-d', $item->publishtime) : '' }}</span>
                </li>
            @empty
                <li class="list-group-item text-muted">暂无新闻</li>
            @endforelse
        </ul>
        <a href="{{ route('portal.category', 'news-center') }}" class="btn btn-sm btn-outline-primary mt-2">更多新闻</a>
    </div>
    <div class="col-lg-5">
        <h2 class="h5 fw-semibold mb-3">客户案例</h2>
        <ul class="list-group list-group-flush portal-card">
            @forelse($latestCases as $item)
                @php $caseCat = $item->category ?? \App\Models\Admin\Category::find($item->category_id); @endphp
                <li class="list-group-item bg-white">
                    @if($caseCat && $item->slug)
                        <a href="{{ route('portal.archive', [$caseCat->nickname, $item->slug]) }}" class="text-decoration-none">{{ $item->title }}</a>
                    @else
                        {{ $item->title }}
                    @endif
                </li>
            @empty
                <li class="list-group-item text-muted">暂无案例</li>
            @endforelse
        </ul>
    </div>
</div>

@if(($tagCloud ?? collect())->isNotEmpty())
<section class="mt-5">
    <h2 class="h6 text-muted mb-2">标签</h2>
    <div class="d-flex flex-wrap gap-2">
        @foreach($tagCloud as $t)
            <a href="{{ route('portal.tag', $t->slug) }}" class="badge rounded-pill text-bg-light border text-decoration-none">{{ $t->name }}</a>
        @endforeach
    </div>
</section>
@endif
@endsection