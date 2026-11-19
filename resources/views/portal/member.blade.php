@extends($portalLayout ?? 'portal.layouts.app')

@section('title', ($member->nickname ?: $member->username).' 的主页')

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    @if($member->avatar)
        <img src="{{ $member->avatar }}" class="rounded-circle" width="64" height="64" alt="">
    @endif
    <div>
        <h1 class="h4 mb-0">{{ $member->nickname ?: $member->username }}</h1>
        <p class="text-muted small mb-0">会员主页</p>
    </div>
</div>
<h2 class="h6 text-muted mb-3">已发布文章</h2>
<ul class="list-group portal-card">
    @forelse($archives as $item)
        @php $cat = $item->category; @endphp
        <li class="list-group-item">
            @if($cat && $item->slug)
                <a href="{{ route('portal.archive', [$cat->nickname, $item->slug]) }}">{{ $item->title }}</a>
            @else
                {{ $item->title }}
            @endif
        </li>
    @empty
        <li class="list-group-item text-muted">暂无公开文章</li>
    @endforelse
</ul>
@endsection