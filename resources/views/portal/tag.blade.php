@extends($portalLayout ?? 'portal.layouts.app')

@section('title', '标签：'.$tag->name)

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('portal.home') }}">首页</a></li>
        <li class="breadcrumb-item active">{{ $tag->name }}</li>
    </ol>
</nav>
<h1 class="h4 fw-semibold mb-3">标签：{{ $tag->name }}</h1>
<div class="row g-3 mb-4">
    @forelse($archives as $item)
        @php $cat = $item->category; @endphp
        <div class="col-md-6">
            @if($cat && $item->slug)
                <a href="{{ route('portal.archive', [$cat->nickname, $item->slug]) }}" class="card portal-card text-decoration-none text-dark">
                    <div class="card-body">
                        <h2 class="h6 mb-1">{{ $item->title }}</h2>
                        <span class="small text-muted">{{ $item->publishtime ? date('Y-m-d', $item->publishtime) : '' }}</span>
                    </div>
                </a>
            @endif
        </div>
    @empty
        <p class="text-muted">该标签下暂无文章。</p>
    @endforelse
</div>
@php $lastPage = (int) ceil($total / max(1, $perPage)); @endphp
@if($lastPage > 1)
    <nav>
        <ul class="pagination pagination-sm">
            @for($p = 1; $p <= $lastPage; $p++)
                <li class="page-item {{ $p === $page ? 'active' : '' }}">
                    <a class="page-link" href="{{ route('portal.tag', ['slug' => $tag->slug, 'page' => $p]) }}">{{ $p }}</a>
                </li>
            @endfor
        </ul>
    </nav>
@endif
@endsection