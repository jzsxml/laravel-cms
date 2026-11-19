@extends($portalLayout ?? 'portal.layouts.app')

@section('title', $category->name.' — '.($site['sitename'] ?: config('app.name')))

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('portal.home') }}">首页</a></li>
        <li class="breadcrumb-item active">{{ $category->name }}</li>
    </ol>
</nav>
<h1 class="h3 fw-semibold mb-2">{{ $category->name }}</h1>
@if($category->description)
    <p class="text-muted">{{ $category->description }}</p>
@endif

<div class="row g-3 mb-4">
    @forelse($archives as $item)
        <div class="col-md-6">
            <a href="{{ route('portal.archive', [$category->nickname, $item->slug]) }}" class="card portal-card h-100 text-decoration-none text-dark">
                <div class="card-body">
                    <h2 class="h6 card-title">{{ $item->title }}</h2>
                    <p class="card-text small text-muted">{{ \Illuminate\Support\Str::limit($item->description, 120) }}</p>
                    <span class="small text-muted">{{ $item->publishtime ? date('Y-m-d', $item->publishtime) : '' }}</span>
                </div>
            </a>
        </div>
    @empty
        <p class="text-muted">该栏目暂无内容。</p>
    @endforelse
</div>

@php $lastPage = (int) ceil($total / max(1, $perPage)); @endphp
@if($lastPage > 1)
    <nav>
        <ul class="pagination pagination-sm">
            @for($p = 1; $p <= $lastPage; $p++)
                <li class="page-item {{ $p === $page ? 'active' : '' }}">
                    <a class="page-link" href="{{ route('portal.category', $category->nickname).'?page='.$p }}">{{ $p }}</a>
                </li>
            @endfor
        </ul>
    </nav>
@endif
@endsection