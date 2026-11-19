@extends($portalLayout ?? 'portal.layouts.app')

@section('title', $special->title)

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('portal.home') }}">首页</a></li>
        <li class="breadcrumb-item active">{{ $special->title }}</li>
    </ol>
</nav>
<h1 class="h3 fw-semibold mb-2">{{ $special->title }}</h1>
@if($special->description)
    <p class="text-muted">{{ $special->description }}</p>
@endif
@if($special->content)
    <div class="portal-article-content mb-4">{!! \App\Support\HtmlSanitizer::clean($special->content) !!}</div>
@endif

@if($filterTags->isNotEmpty())
    <div class="mb-3 d-flex flex-wrap gap-2">
        <a href="{{ route('portal.special', $special->slug) }}" class="badge rounded-pill {{ $activeTag === '' ? 'text-bg-primary' : 'text-bg-light border text-decoration-none' }}">全部</a>
        @foreach($filterTags as $t)
            <a href="{{ route('portal.special', $special->slug).'?tag='.$t->slug }}" class="badge rounded-pill {{ $activeTag === $t->slug ? 'text-bg-primary' : 'text-bg-light border text-decoration-none' }}">{{ $t->name }}</a>
        @endforeach
    </div>
@endif

<div class="row g-3">
    @forelse($archives as $item)
        @php $cat = $item->category; @endphp
        <div class="col-md-6">
            @if($cat && $item->slug)
            <a href="{{ route('portal.archive', [$cat->nickname, $item->slug]) }}" class="card portal-card text-decoration-none text-dark">
                <div class="card-body">
                    <h2 class="h6">{{ $item->title }}</h2>
                    <p class="small text-muted mb-0">{{ \Illuminate\Support\Str::limit($item->description, 100) }}</p>
                </div>
            </a>
            @endif
        </div>
    @empty
        <p class="text-muted">暂无文章</p>
    @endforelse
</div>
@endsection