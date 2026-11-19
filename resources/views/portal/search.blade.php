@extends($portalLayout ?? 'portal.layouts.app')

@section('title', '搜索 — '.($site['sitename'] ?: config('app.name')))

@section('content')
<h1 class="h4 fw-semibold mb-3">搜索结果</h1>
<form class="mb-4" action="{{ route('portal.search') }}" method="get">
    <div class="input-group">
        <input type="search" name="q" class="form-control" value="{{ $q }}" placeholder="关键词" required>
        <button class="btn btn-primary" type="submit">搜索</button>
    </div>
</form>

@if($q === '')
    <p class="text-muted">请输入关键词。</p>
@elseif($archives->isEmpty())
    <p class="text-muted">未找到与「{{ $q }}」相关的内容。</p>
@else
    <p class="small text-muted">共 {{ $total }} 条</p>
    <ul class="list-group portal-card">
        @foreach($archives as $item)
            @php $cat = $item->category; @endphp
            <li class="list-group-item">
                @if($cat && $item->slug)
                    <a href="{{ route('portal.archive', [$cat->nickname, $item->slug]) }}">{{ $item->title }}</a>
                @else
                    {{ $item->title }}
                @endif
                <div class="small text-muted">{{ \Illuminate\Support\Str::limit($item->description, 100) }}</div>
            </li>
        @endforeach
    </ul>
    @php $lastPage = (int) ceil($total / max(1, $perPage)); @endphp
    @if($lastPage > 1)
        <nav class="mt-3">
            <ul class="pagination pagination-sm">
                @for($p = 1; $p <= $lastPage; $p++)
                    <li class="page-item {{ $p === $page ? 'active' : '' }}">
                        <a class="page-link" href="{{ route('portal.search', ['q' => $q, 'page' => $p]) }}">{{ $p }}</a>
                    </li>
                @endfor
            </ul>
        </nav>
    @endif
@endif
@endsection