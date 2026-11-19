@extends($portalLayout ?? 'portal.themes.cms.layouts.app')

@section('title', '首页 — '.($site['sitename'] ?: config('app.name')))

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        @if(($focusSlides ?? collect())->isNotEmpty())
        <div id="cmsFocus" class="carousel slide cms-focus-carousel mb-4 bg-white border" data-bs-ride="carousel">
            <div class="carousel-indicators">
                @foreach($focusSlides as $i => $slide)
                    @php $sc = $slide->category; @endphp
                    <button type="button" data-bs-target="#cmsFocus" data-bs-slide-to="{{ $i }}" @class(['active' => $i === 0]) aria-label="幻灯 {{ $i + 1 }}"></button>
                @endforeach
            </div>
            <div class="carousel-inner">
                @foreach($focusSlides as $i => $slide)
                    @php
                        $sc = $slide->category;
                        $link = ($sc && $slide->slug) ? route('portal.archive', [$sc->nickname, $slide->slug]) : '#';
                        $img = $slide->image ?: 'https://picsum.photos/seed/focus'.$slide->id.'/1200/360';
                    @endphp
                    <div class="carousel-item @if($i === 0) active @endif">
                        <a href="{{ $link }}"><img src="{{ $img }}" class="d-block w-100" alt="{{ $slide->title }}"></a>
                        <div class="carousel-caption">
                            <h3 class="h5 mb-0">{{ $slide->title }}</h3>
                        </div>
                    </div>
                @endforeach
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#cmsFocus" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#cmsFocus" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
        @endif

        @if(($hotGallery ?? collect())->isNotEmpty())
        <section class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="cms-section-title mb-0">热门图集</h2>
                <a href="{{ route('portal.search') }}" class="small text-decoration-none">查看更多</a>
            </div>
            <div class="row g-2 cms-hot-gallery">
                @foreach($hotGallery as $item)
                    @php $hc = $item->category; $hurl = ($hc && $item->slug) ? route('portal.archive', [$hc->nickname, $item->slug]) : '#'; @endphp
                    <div class="col-6 col-md-3">
                        <a href="{{ $hurl }}" class="card h-100 text-decoration-none text-dark border">
                            <img src="{{ $item->image ?: 'https://picsum.photos/seed/hot'.$item->id.'/400/260' }}" class="card-img-top" alt="">
                            <div class="card-body p-2">
                                <h5 class="card-title small mb-0 text-truncate">{{ $item->title }}</h5>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
        @endif

        <section class="bg-white border p-3 mb-3">
            <h2 class="cms-section-title">最近更新</h2>
            @if(($tabCategories ?? collect())->isNotEmpty())
            <ul class="nav nav-tabs nav-tabs-sm flex-wrap border-0 mb-3 gap-1">
                @foreach($tabCategories as $tab)
                    <li class="nav-item">
                        <a class="nav-link py-1 px-2 small {{ $loop->first ? 'active' : '' }}" href="{{ route('portal.category', $tab->nickname) }}">{{ $tab->name }}</a>
                    </li>
                @endforeach
            </ul>
            @endif
            <div class="cms-article-list">
                @forelse($recentList ?? [] as $item)
                    @include('portal.partials.cms-archive-item', ['item' => $item])
                @empty
                    <p class="text-muted mb-0">暂无文章，请在后台发布内容。</p>
                @endforelse
            </div>
            @php $moreCat = ($tabCategories ?? collect())->first(); @endphp
            @if($moreCat)
                <div class="text-center mt-3">
                    <a href="{{ route('portal.category', $moreCat->nickname) }}" class="btn btn-outline-primary btn-sm">加载更多</a>
                </div>
            @endif
        </section>
    </div>

    <aside class="col-lg-4">
        <div class="cms-sidebar-panel">
            <div class="panel-head">最新更新</div>
            <div class="panel-body">
                <ul class="cms-sidebar-list">
                    @foreach($sidebarLatest ?? [] as $item)
                        @php $lc = $item->category; @endphp
                        <li>
                            @if($lc && $item->slug)
                                <a href="{{ route('portal.archive', [$lc->nickname, $item->slug]) }}">
                                    <span class="text-primary">[ {{ $lc->name }} ]</span> {{ $item->title }}
                                </a>
                            @else
                                {{ $item->title }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="cms-sidebar-panel">
            <div class="panel-head">推荐资讯</div>
            <div class="panel-body">
                <ul class="cms-sidebar-list">
                    @foreach($sidebarRecommend ?? [] as $item)
                        @php $rc = $item->category; @endphp
                        <li>
                            @if($rc && $item->slug)
                                <a href="{{ route('portal.archive', [$rc->nickname, $item->slug]) }}">{{ $item->title }}</a>
                            @else
                                {{ $item->title }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        @if(($tagCloud ?? collect())->isNotEmpty())
        <div class="cms-sidebar-panel">
            <div class="panel-head">热门标签</div>
            <div class="panel-body cms-tag-cloud">
                @foreach($tagCloud as $t)
                    <a href="{{ route('portal.tag', $t->slug) }}">{{ $t->name }}</a>
                @endforeach
            </div>
        </div>
        @endif

        @if(($downloadList ?? collect())->isNotEmpty())
        <div class="cms-sidebar-panel">
            <div class="panel-head">推荐下载</div>
            <div class="panel-body">
                <ul class="cms-sidebar-list">
                    @foreach($downloadList as $item)
                        @php $dc = $item->category; @endphp
                        <li>
                            @if($dc && $item->slug)
                                <a href="{{ route('portal.archive', [$dc->nickname, $item->slug]) }}">{{ $item->title }}</a>
                            @else
                                {{ $item->title }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        @php $friendBlock = \App\Support\CmsBlock::html('friend_links'); @endphp
        @if($friendBlock !== '')
        <div class="cms-sidebar-panel">
            <div class="panel-head">友情链接</div>
            <div class="panel-body">{!! $friendBlock !!}</div>
        </div>
        @endif
    </aside>
</div>
@endsection