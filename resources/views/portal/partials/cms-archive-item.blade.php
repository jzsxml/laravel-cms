@php
    $cat = $item->category;
    $url = ($cat && $item->slug) ? route('portal.archive', [$cat->nickname, $item->slug]) : null;
    $thumb = $item->image ?: 'https://picsum.photos/seed/'.($item->id ?? 1).'/360/240';
    $commentsCount = $item->comments_count ?? 0;
@endphp
<article class="cms-article-item">
    @if($url)
        <a href="{{ $url }}" class="cms-article-thumb">
            <img src="{{ $thumb }}" alt="{{ $item->title }}" loading="lazy">
        </a>
    @else
        <span class="cms-article-thumb">
            <img src="{{ $thumb }}" alt="" loading="lazy">
        </span>
    @endif
    <div class="flex-grow-1 min-w-0">
        <h3>
            @if($url)
                <a href="{{ $url }}">{{ $item->title }}</a>
            @else
                {{ $item->title }}
            @endif
        </h3>
        @if($item->description)
            <p class="cms-article-excerpt">{{ $item->description }}</p>
        @endif
        <div class="cms-article-meta">
            @if($cat)
                <a href="{{ route('portal.category', $cat->nickname) }}" class="cat">{{ $cat->name }}</a>
            @endif
            @if($item->publishtime)
                <span>{{ date('Y年m月d日', $item->publishtime) }}</span>
            @endif
            <span><i class="far fa-thumbs-up me-1"></i>{{ (int) $item->likes }} 点赞</span>
            <span><i class="far fa-comment me-1"></i>{{ $commentsCount }} 评论</span>
            <span><i class="far fa-eye me-1"></i>{{ (int) $item->views }} 浏览</span>
        </div>
    </div>
</article>