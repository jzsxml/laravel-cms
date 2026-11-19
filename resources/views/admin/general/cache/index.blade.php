@extends('admin.layouts.app')

@section('title', '更新缓存')

@section('content')
<div class="alert alert-info py-2 small border-0 mb-3">
    将系统配置、菜单规则、栏目分类等常用数据写入缓存（TTL {{ $ttl }} 秒）。修改配置或菜单后会自动清理相关项；也可在此手动全部刷新。
</div>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card cms-card">
            <div class="card-header">缓存项说明</div>
            <ul class="list-group list-group-flush">
                @foreach($catalog as $key => $label)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>{{ $label }}</span>
                        <code class="small text-muted">{{ $key }}</code>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card cms-card">
            <div class="card-body">
                <p class="mb-2 text-muted small">上次全量刷新</p>
                <p class="fs-5 mb-3" id="last-refresh">
                    @if(!empty($meta['refreshed_at']))
                        {{ date('Y-m-d H:i:s', (int) $meta['refreshed_at']) }}
                    @else
                        <span class="text-secondary">尚未记录（点击刷新后更新）</span>
                    @endif
                </p>
                <button type="button" class="btn btn-primary" id="btn-refresh">
                    <i class="fas fa-sync-alt me-1"></i> 更新缓存
                </button>
                <p class="mt-3 mb-0 small text-muted">命令行：<code>php artisan cms:cache-refresh</code></p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const token = document.querySelector('meta[name=csrf-token]').content;
    const url = @json(url(config('cms.admin_path').'/general/cache/refresh'));
    const btn = document.getElementById('btn-refresh');
    const lastEl = document.getElementById('last-refresh');

    btn.addEventListener('click', async () => {
        btn.disabled = true;
        try {
            const body = new URLSearchParams({ _token: token });
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body,
            });
            const json = await res.json();
            alert(json.msg || (json.code === 1 ? '完成' : '失败'));
            if (json.code === 1 && json.data?.refreshed_at) {
                const d = new Date(json.data.refreshed_at * 1000);
                const pad = n => String(n).padStart(2, '0');
                lastEl.textContent = `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
            }
        } catch (e) {
            alert('请求失败');
        } finally {
            btn.disabled = false;
        }
    });
})();
</script>
@endpush