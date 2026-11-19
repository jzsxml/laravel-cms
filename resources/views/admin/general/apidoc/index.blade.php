@extends('admin.layouts.app')

@section('title', $title)

@section('content')
<div class="card cms-card mb-3">
    <div class="card-body">
        <p class="text-muted small mb-2">会员接口鉴权请求头：<code>{{ $tokenHeader }}</code> 或参数 <code>token</code></p>
        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-copy-md">复制 Markdown</button>
    </div>
</div>
<div class="card cms-card">
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0" id="api-table">
            <thead>
            <tr><th>方法</th><th>路径</th><th>说明</th></tr>
            </thead>
            <tbody>
            @foreach($routes as $r)
                <tr>
                    <td><span class="badge bg-secondary">{{ $r['methods'] }}</span></td>
                    <td><code>{{ $r['uri'] }}</code></td>
                    <td class="small text-muted">{{ class_basename($r['action']) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('btn-copy-md')?.addEventListener('click', () => {
    const lines = ['| 方法 | 路径 |', '| --- | --- |'];
    document.querySelectorAll('#api-table tbody tr').forEach(tr => {
        const cells = [...tr.querySelectorAll('td')].map(td => td.innerText.trim());
        lines.push(`| ${cells[0]} | \`${cells[1]}\` |`);
    });
    navigator.clipboard.writeText(lines.join('\n'));
    alert('已复制 Markdown 表格');
});
</script>
@endpush