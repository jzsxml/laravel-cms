@php
    $locked = $contentView['locked'] ?? false;
    $price = (float) ($contentView['price'] ?? 0);
@endphp
<div class="portal-article-content">
    {!! $contentView['html'] ?? '' !!}
</div>
@if($locked)
    <div class="alert alert-warning mt-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <span>本文为付费内容，试读已结束。价格 <strong>¥{{ number_format($price, 2) }}</strong>
            @if(!empty($member) && (int) ($member->vip_expiretime ?? 0) > time())
                <span class="badge text-bg-success ms-1">VIP 有效</span>
            @endif
        </span>
        @if(empty($member))
            <a href="{{ route('member.login') }}" class="btn btn-sm btn-primary">登录后购买</a>
        @else
            <div class="d-flex gap-2">
                <form method="post" action="{{ $purchaseUrl }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary">余额支付解锁</button>
                </form>
                <a href="{{ route('member.wallet') }}" class="btn btn-sm btn-outline-secondary">去充值</a>
            </div>
        @endif
    </div>
@endif
@if(!empty($canDownload) && !empty($downloadUrl))
    <div class="mt-3">
        <a href="{{ $downloadUrl }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-download me-1"></i>下载附件</a>
    </div>
@endif
@if(!empty($likeUrl))
    <div class="mt-3 d-flex align-items-center gap-2 small text-muted">
        <form method="post" action="{{ $likeUrl }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="far fa-thumbs-up me-1"></i>点赞 {{ $likes ?? 0 }}</button>
        </form>
    </div>
@endif