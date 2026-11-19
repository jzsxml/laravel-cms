@extends('admin.layouts.app')

@section('title', '本地插件')

@section('content')
<div class="alert alert-info py-2 small border-0">
    仅管理 <code>addons/</code> 目录下的本地插件，不包含应用市场或在线安装。
</div>
<div class="card cms-card cms-table-wrap">
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped mb-0">
            <thead>
            <tr>
                <th>标识</th>
                <th>名称</th>
                <th>版本</th>
                <th>已安装</th>
                <th>已启用</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody id="tbody"></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const token = document.querySelector('meta[name=csrf-token]').content;
    const base = @json(url(config('cms.admin_path').'/general/addon'));

    async function load() {
        const res = await fetch(base, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
        const data = await res.json();
        const tbody = document.getElementById('tbody');
        tbody.innerHTML = (data.rows || []).map(row => `
            <tr>
                <td>${row.name}</td>
                <td>${row.title || ''}<div class="small text-muted">${row.intro || ''}</div></td>
                <td>${row.version || ''}</td>
                <td>${row.installed ? '是' : '否'}</td>
                <td>${row.enabled ? '是' : '否'}</td>
                <td class="text-nowrap">
                    <button class="btn btn-sm btn-success" data-act="install" data-name="${row.name}">安装</button>
                    <button class="btn btn-sm btn-primary" data-act="enable" data-name="${row.name}">启用</button>
                    <button class="btn btn-sm btn-secondary" data-act="disable" data-name="${row.name}">禁用</button>
                    <button class="btn btn-sm btn-outline-danger" data-act="uninstall" data-name="${row.name}">卸载</button>
                </td>
            </tr>
        `).join('');
        tbody.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('click', async () => {
                const act = btn.getAttribute('data-act');
                const name = btn.getAttribute('data-name');
                const body = new URLSearchParams({ name, _token: token });
                const headers = { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' };
                let res;
                try {
                    res = await fetch(`${base}/${act}`, { method: 'POST', headers, body });
                } catch (e) {
                    alert('请求失败，请检查网络');
                    return;
                }
                let json;
                try {
                    json = await res.json();
                } catch (e) {
                    alert(res.status === 419 ? '会话已过期，请刷新页面后重试' : `操作失败 (HTTP ${res.status})`);
                    return;
                }
                alert(json.msg || (json.code === 1 ? '完成' : '操作失败'));
                if (json.code === 1) load();
            });
        });
    }

    load();
})();
</script>
@endpush