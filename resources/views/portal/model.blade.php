@extends($portalLayout ?? 'portal.layouts.app')

@section('title', $model->title.' — '.($site['sitename'] ?: config('app.name')))

@section('content')
<h1 class="h3 fw-semibold mb-4">{{ $model->title }}</h1>
<div class="row g-3">
    @forelse($items as $item)
        <div class="col-md-6">
            <div class="card portal-card h-100">
                <div class="card-body">
                    <h2 class="h6">{{ $item['title'] }}</h2>
                    <dl class="small mb-0">
                        @foreach($item['data'] as $k => $v)
                            @if($v !== '' && $v !== null)
                                <dt class="text-muted d-inline">{{ $k }}:</dt>
                                <dd class="d-inline ms-1 me-2">{{ is_scalar($v) ? $v : json_encode($v, JSON_UNESCAPED_UNICODE) }}</dd>
                            @endif
                        @endforeach
                    </dl>
                </div>
            </div>
        </div>
    @empty
        <p class="text-muted">暂无数据</p>
    @endforelse
</div>
@endsection