@extends($portalLayout ?? 'portal.themes.cms.layouts.app')

@section('title', $category->name.' — '.($site['sitename'] ?: config('app.name')))

@section('content')
<nav aria-label="breadcrumb" class="mb-3 small">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('portal.home') }}">首页</a></li>
        <li class="breadcrumb-item active">{{ $category->name }}</li>
    </ol>
</nav>

<div class="bg-white border p-3 mb-3">
    <h1 class="h4 cms-section-title mb-1">{{ $category->name }}</h1>
    @if($category->description)
        <p class="text-muted small mb-0">{{ $category->description }}</p>
    @endif
</div>

<div class="bg-white border p-3 mb-3">
    @forelse($archives as $item)
        @include('portal.partials.cms-archive-item', ['item' => $item])
    @empty
        <p class="text-muted mb-0">该栏目暂无内容。</p>
    @endforelse
</div>

@php $lastPage = (int) ceil($total / max(1, $perPage)); @endphp
@if($lastPage > 1)
    <nav>
        <ul class="pagination pagination-sm justify-content-center">
            @for($p = 1; $p <= $lastPage; $p++)
                <li class="page-item {{ $p === $page ? 'active' : '' }}">
                    <a class="page-link" href="{{ route('portal.category', $category->nickname).'?page='.$p }}">{{ $p }}</a>
                </li>
            @endfor
        </ul>
    </nav>
@endif
@endsection