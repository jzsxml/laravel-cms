@extends('admin.layouts.app')

@section('title', $title)

@section('content')
@include('admin.partials.cms-adv-filters', ['filterFields' => $filterFields ?? []])

<div class="cms-toolbar cms-toolbar-primary d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <div class="input-group input-group-sm cms-search-group">
            <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
            <input type="search" class="form-control" id="search" placeholder="搜索用户名、标题、IP、URL…" style="min-width:200px;max-width:320px">
            <button class="btn btn-primary" type="button" id="btn-search">搜索</button>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#cms-adv-filter">
            <i class="fas fa-sliders-h me-1"></i>筛选
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-refresh" title="刷新">
            <i class="fas fa-sync-alt" id="btn-refresh-icon"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-export">
            <i class="fas fa-file-export me-1"></i>导出
        </button>
    </div>
    <div class="d-flex gap-1">
        <button type="button" class="btn btn-sm btn-outline-info cms-quick-filter" data-field="title" data-op="LIKE" data-value="登录">仅登录</button>
        <button type="button" class="btn btn-sm btn-outline-secondary cms-quick-filter" data-field="title" data-op="LIKE" data-value="退出">仅退出</button>
    </div>
</div>

<div class="cms-table-wrap cms-table-host" id="cms-table-host">
    <div class="cms-table-loading d-none" id="cms-table-loading">
        <div class="cms-table-spinner"></div>
        <span>加载中…</span>
    </div>
    <div class="table-responsive cms-table-scroll">
        <table class="table table-hover table-sm mb-0 cms-data-table">
            <thead>
            <tr>
                @foreach($listFields as $f)
                    <th class="cms-sort-th" data-sort-field="{{ $f }}">
                        <span>{{ $listFieldLabels[$f] ?? $f }}</span>
                        <i class="fas fa-sort cms-sort-icon text-muted"></i>
                    </th>
                @endforeach
            </tr>
            </thead>
            <tbody id="tbody"></tbody>
        </table>
    </div>
    <div class="cms-table-footer d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 px-1">
        <div class="small text-muted" id="table-total">共 0 条</div>
        <div class="d-flex align-items-center gap-2">
            <label class="small text-muted mb-0">每页</label>
            <select id="page-size" class="form-select form-select-sm" style="width:auto">
                @foreach([10, 15, 20, 50, 100] as $ps)
                    <option value="{{ $ps }}" @selected($ps === 15)>{{ $ps }}</option>
                @endforeach
            </select>
            <ul class="pagination pagination-sm mb-0" id="pager"></ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/cms-table.js') }}"></script>
<script>
(() => {
    const base = @json(url(config('cms.admin_path').'/'.$urlPrefix));
    const listFields = @json($listFields);

    function rowHtml(row) {
        const cells = listFields.map(f => {
            let v = row[f] ?? '';
            if (f === 'title' && typeof v === 'string' && /登录|login/i.test(v)) {
                v = `<span class="badge cms-badge-ok">${v}</span>`;
            }
            return `<td data-label="${f}">${v}</td>`;
        }).join('');
        return `<tr class="cms-table-row">${cells}</tr>`;
    }

    const tableApi = CmsTable.init({
        base,
        rowHtml,
        listFields,
        limit: 15,
    });

    document.querySelectorAll('.cms-quick-filter').forEach((btn) => {
        btn.addEventListener('click', () => {
            const field = btn.getAttribute('data-field');
            const op = btn.getAttribute('data-op') || 'LIKE';
            const value = btn.getAttribute('data-value') || '';
            const item = document.querySelector(`.cms-filter-item[data-field="${field}"]`);
            if (item) {
                const opEl = item.querySelector('.cms-filter-op');
                const valEl = item.querySelector('.cms-filter-val');
                if (opEl) opEl.value = op;
                if (valEl) valEl.value = value;
            }
            document.getElementById('btn-apply-filter')?.click();
        });
    });
})();
</script>
@endpush