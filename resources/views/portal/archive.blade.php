@extends($portalLayout ?? 'portal.layouts.app')

@section('title', $archive->title.' — '.($site['sitename'] ?: config('app.name')))

@push('meta')
    @if($archive->keywords)
        <meta name="keywords" content="{{ $archive->keywords }}">
    @endif
    @if($archive->description)
        <meta name="description" content="{{ $archive->description }}">
    @endif
    <link rel="canonical" href="{{ route('portal.archive', [$category->nickname, $archive->slug]) }}">
@endpush

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('portal.home') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('portal.category', $category->nickname) }}">{{ $category->name }}</a></li>
        <li class="breadcrumb-item active">{{ $archive->title }}</li>
    </ol>
</nav>

<article class="card portal-card border-0">
    <div class="card-body p-4">
        <h1 class="h2 fw-semibold mb-2">{{ $archive->title }}</h1>
        @if($archive->subtitle)
            <p class="lead text-muted">{{ $archive->subtitle }}</p>
        @endif
        <p class="small text-muted mb-2">
            {{ $archive->publishtime ? date('Y-m-d H:i', $archive->publishtime) : '' }}
            · 阅读 {{ $archive->views }}
            @if(($archive->pay_type ?? 'free') === 'pay' && (float) $archive->price > 0)
                · <span class="badge text-bg-warning text-dark">付费 ¥{{ number_format((float) $archive->price, 2) }}</span>
            @endif
        </p>
        @if($archive->tags->isNotEmpty())
            <p class="mb-4">
                @foreach($archive->tags as $t)
                    <a href="{{ route('portal.tag', $t->slug) }}" class="badge rounded-pill text-bg-light border text-decoration-none me-1">{{ $t->name }}</a>
                @endforeach
            </p>
        @endif
        @php($cover = \App\Support\SafeUrl::forDisplay($archive->image ?? ''))
        @if($cover !== '')
            <img src="{{ $cover }}" class="img-fluid rounded mb-4" alt="">
        @endif
        @include('portal.partials.paywall', [
            'contentView' => $contentView,
            'canDownload' => $canDownload,
            'member' => $member,
            'purchaseUrl' => route('portal.purchase.archive', [$category->nickname, $archive->slug]),
            'downloadUrl' => route('portal.download.archive', [$category->nickname, $archive->slug]),
            'likeUrl' => route('portal.like.archive', [$category->nickname, $archive->slug]),
            'likes' => $archive->likes,
        ])
    </div>
</article>

<section class="mt-4">
    <h2 class="h5 mb-3">评论</h2>
    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif
    <ul class="list-group portal-card mb-3">
        @forelse($comments as $c)
            <li class="list-group-item">
                <strong class="small">{{ $c->member_nickname }}</strong>
                <span class="text-muted small ms-2">{{ $c->createtime ? date('Y-m-d H:i', $c->createtime) : '' }}</span>
                <p class="mb-0 mt-1">{{ $c->content }}</p>
            </li>
        @empty
            <li class="list-group-item text-muted">暂无评论</li>
        @endforelse
    </ul>
    <p class="small text-muted">评论需 <a href="{{ route('member.login') }}">登录会员</a> 后提交。</p>
    <form method="post" action="{{ route('portal.comment', [$category->nickname, $archive->slug]) }}" class="card portal-card p-3">
        @csrf
        <textarea name="content" class="form-control mb-2" rows="3" maxlength="1000" required placeholder="写下你的看法…"></textarea>
        <button type="submit" class="btn btn-primary btn-sm">发表评论</button>
    </form>
</section>
@endsection