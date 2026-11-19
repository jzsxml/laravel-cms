<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('portal.home') }}">首页</a></li>
        <li class="breadcrumb-item active">{{ $page->title }}</li>
    </ol>
</nav>
<article class="card portal-card border-0">
    <div class="card-body p-4">
        <h1 class="h2 fw-semibold mb-3">{{ $page->title }}</h1>
        <div class="row g-4">
            <div class="col-md-5">
                <ul class="list-unstyled small text-muted">
                    @if(!empty($site['site_contact_phone']))
                        <li class="mb-2"><i class="fas fa-phone me-2"></i>{{ $site['site_contact_phone'] }}</li>
                    @endif
                    @if(!empty($site['site_contact_email']))
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i>{{ $site['site_contact_email'] }}</li>
                    @endif
                </ul>
            </div>
            <div class="col-md-7 portal-article-content">
                {!! \App\Support\HtmlSanitizer::clean($page->content) !!}
            </div>
        </div>
    </div>
</article>