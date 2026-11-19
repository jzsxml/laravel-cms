<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('portal.home') }}">首页</a></li>
        <li class="breadcrumb-item active">{{ $page->title }}</li>
    </ol>
</nav>
<article class="card portal-card border-0">
    <div class="card-body p-4">
        <h1 class="h2 fw-semibold mb-3">{{ $page->title }}</h1>
        @if($page->image)
            <img src="{{ $page->image }}" class="img-fluid rounded mb-4" alt="">
        @endif
        @include('portal.partials.paywall', [
            'purchaseUrl' => route('portal.purchase.page', $page->slug),
            'downloadUrl' => route('portal.download.page', $page->slug),
            'likeUrl' => null,
            'likes' => 0,
            'contentView' => $contentView ?? ['html' => $page->content, 'locked' => false],
            'canDownload' => $canDownload ?? false,
            'member' => $member ?? null,
        ])
    </div>
</article>