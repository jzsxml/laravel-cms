@extends('admin.layouts.app')

@section('title', $title)

@section('content')
@if(!empty($headingFilterField) && !empty($headingFilterOptions))
    <ul class="nav nav-pills cms-pill-filter mb-3" id="status-filter">
        @foreach($headingFilterOptions as $val => $label)
            <li class="nav-item">
                <a href="#" class="nav-link {{ (string)$val === '' ? 'active' : '' }}" data-value="{{ $val }}">{{ $label }}</a>
            </li>
        @endforeach
    </ul>
@endif

@include('admin.partials.cms-adv-filters', ['filterFields' => $filterFields ?? []])

<div class="cms-toolbar cms-toolbar-primary d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <div class="input-group input-group-sm cms-search-group">
            <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
            <input type="search" class="form-control" id="search" placeholder="{{ __('admin.ui.search') }}" style="min-width:160px;max-width:240px">
            <button class="btn btn-primary" type="button" id="btn-search">{{ __('admin.ui.search_btn') }}</button>
        </div>
        @if(!empty($filterFields))
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#cms-adv-filter">
                <i class="fas fa-sliders-h me-1"></i>{{ __('admin.ui.filter') }}
            </button>
        @endif
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-refresh" title="{{ __('admin.ui.refresh') }}">
            <i class="fas fa-sync-alt" id="btn-refresh-icon"></i>
        </button>
    </div>
    <div class="d-flex flex-wrap gap-2 align-items-center">
        @if($showAdd ?? true)
        <a href="{{ url(config('cms.admin_path').'/'.$urlPrefix.'/add') }}{{ !empty($extraQuery) ? '?'.http_build_query($extraQuery) : '' }}" class="btn btn-sm btn-success">
            <i class="fas fa-plus me-1"></i>{{ __('admin.ui.add') }}
        </a>
        @endif
        @if(!empty($showRecyclebin))
            <a href="{{ url(config('cms.admin_path').'/'.$urlPrefix.'/recyclebin') }}{{ !empty($extraQuery) ? '?'.http_build_query($extraQuery) : '' }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-trash-restore me-1"></i>{{ __('admin.ui.recyclebin') }}
            </a>
        @endif
        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-export" title="CSV">
            <i class="fas fa-file-csv me-1"></i>CSV
        </button>
        <button type="button" class="btn btn-sm btn-outline-success" id="btn-export-xlsx" title="Excel">
            <i class="fas fa-file-excel me-1"></i>Excel
        </button>
    </div>
</div>

<div class="cms-toolbar cms-batch-bar mb-3">
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <span class="small text-muted"><i class="fas fa-layer-group me-1"></i>{{ __('admin.ui.batch') }}</span>
        <select id="batch-field" class="form-select form-select-sm" style="width:140px">
            @foreach($listFields as $f)
                @if(in_array($f, ['status','weigh','switch','flag','category_id'], true))
                    <option value="{{ $f }}">{{ $listFieldLabels[$f] ?? $f }}</option>
                @endif
            @endforeach
        </select>
        <input type="text" id="batch-value" class="form-control form-control-sm" style="width:120px" placeholder="{{ __('admin.ui.batch_value') }}">
        <button type="button" class="btn btn-sm btn-warning text-dark" id="btn-batch">{{ __('admin.ui.batch_update') }}</button>
    </div>
</div>

@if(!empty($archiveBatch))
<div class="cms-toolbar mb-3 d-flex flex-wrap gap-2 align-items-center">
    <span class="small text-muted">运营</span>
    <button type="button" class="btn btn-sm btn-success" id="btn-approve">审核通过</button>
    <button type="button" class="btn btn-sm btn-outline-danger" id="btn-reject">拒稿</button>
    <select id="batch-special" class="form-select form-select-sm" style="width:160px">
        <option value="">加入专题…</option>
        @foreach($specials ?? [] as $sp)
            <option value="{{ $sp->id }}">{{ $sp->title }}</option>
        @endforeach
    </select>
    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-batch-special">执行</button>
    <input type="text" id="batch-tags" class="form-control form-control-sm" style="width:160px" placeholder="追加标签">
    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-batch-tags">打标</button>
</div>
@endif

<div class="cms-table-wrap cms-table-host" id="cms-table-host">
    <div class="cms-table-loading d-none" id="cms-table-loading" aria-hidden="true">
        <div class="cms-table-spinner"></div>
        <span>{{ __('admin.ui.loading') }}</span>
    </div>
    <div class="table-responsive cms-table-scroll">
        <table class="table table-hover table-sm mb-0 cms-data-table">
            <thead>
            <tr>
                <th class="col-check"><input type="checkbox" id="check-all" class="form-check-input"></th>
                @foreach($listFields as $f)
                    <th class="cms-sort-th" data-sort-field="{{ $f }}">
                        <span>{{ $listFieldLabels[$f] ?? $f }}</span>
                        <i class="fas fa-sort cms-sort-icon text-muted"></i>
                    </th>
                @endforeach
                <th class="col-actions">{{ __('admin.ui.operations') }}</th>
            </tr>
            </thead>
            <tbody id="tbody"></tbody>
        </table>
    </div>
    <div class="cms-table-footer d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 px-1">
        <div class="small text-muted" id="table-total">{{ __('admin.ui.total', ['count' => 0]) }}</div>
        <div class="d-flex align-items-center gap-2">
            <label class="small text-muted mb-0">{{ __('admin.ui.per_page') }}</label>
            <select id="page-size" class="form-select form-select-sm" style="width:auto">
                @foreach([10, 15, 20, 50, 100] as $ps)
                    <option value="{{ $ps }}" @selected($ps === 10)>{{ $ps }}</option>
                @endforeach
            </select>
            <ul class="pagination pagination-sm mb-0" id="pager"></ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
    $headingFilter = !empty($headingFilterField)
        ? ['field' => $headingFilterField, 'selector' => '#status-filter .nav-link']
        : null;
    $adminI18n = [
        'confirmDelete' => __('admin.ui.confirm_delete'),
        'noData' => __('admin.ui.no_data'),
        'loadFail' => __('admin.ui.load_fail'),
        'total' => __('admin.ui.total', ['count' => '__COUNT__']),
        'totalRange' => __('admin.ui.total_range', ['total' => '__TOTAL__', 'from' => '__FROM__', 'to' => '__TO__']),
    ];
@endphp
<script src="{{ asset('js/cms-table.js') }}"></script>
<script>
(() => {
    const base = @json(url(config('cms.admin_path').'/'.$urlPrefix));
    const extraQuery = @json($extraQuery ?? null);
    const extraSuffix = extraQuery ? '&' + new URLSearchParams(extraQuery).toString() : '';
    const listFields = @json($listFields);
    const listFieldLabels = @json($listFieldLabels ?? []);
    const adminI18n = @json($adminI18n);
    const token = document.querySelector('meta[name=csrf-token]').content;

    function rowHtml(row) {
        const cells = listFields.map(f => {
            let v = row[f] ?? '';
            if (f === 'status' && (v === 'normal' || v === 'hidden')) {
                v = v === 'normal' ? '<span class="badge cms-badge-ok">正常</span>' : '<span class="badge cms-badge-muted">隐藏</span>';
            } else if (f === 'switch' || f === '开' || v === '关') {
                /* transformed rows may already be 开/关 */
            } else if (typeof v === 'string' && v.startsWith('http')) {
                v = `<a href="${v}" target="_blank" rel="noopener" class="small">${v.length > 40 ? v.slice(0, 40) + '…' : v}</a>`;
            }
            const label = listFieldLabels[f] || f;
            return `<td data-label="${label}">${v}</td>`;
        }).join('');
        return `<tr class="cms-table-row">
            <td class="col-check"><input type="checkbox" class="form-check-input row-check" value="${row.id}"></td>
            ${cells}
            <td class="col-actions text-nowrap">
                <a class="btn btn-xs btn-soft-primary" href="${base}/edit?ids=${row.id}${extraSuffix}"><i class="fas fa-edit"></i></a>
                <button type="button" class="btn btn-xs btn-soft-danger" data-del="${row.id}"><i class="fas fa-trash-alt"></i></button>
            </td>
        </tr>`;
    }

    let tableApi;
    tableApi = CmsTable.init({
        base,
        extraQuery,
        exportXlsxUrl: `${base}/export`,
        rowHtml,
        listFields,
        listFieldLabels,
        i18n: adminI18n,
        headingFilter: @json($headingFilter),
        onBind(tbody) {
            tbody.querySelectorAll('[data-del]').forEach(btn => {
                btn.onclick = async () => {
                    if (!confirm(adminI18n.confirmDelete)) return;
                    const body = new URLSearchParams({ ids: btn.getAttribute('data-del'), _token: token });
                    if (extraQuery) {
                        Object.entries(extraQuery).forEach(([k, v]) => body.set(k, v));
                    }
                    const res = await fetch(`${base}/del`, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body });
                    const data = await res.json();
                    alert(data.msg || '完成');
                    if (data.code === 1) tableApi.reload();
                };
            });
        },
    });

    @if(!empty($archiveBatch))
    const selectedIds = () => [...document.querySelectorAll('.row-check:checked')].map(c => c.value).join(',');
    const postAction = async (url, extra = {}) => {
        const ids = selectedIds();
        if (!ids) return alert('请先勾选行');
        const body = new URLSearchParams({ ids, _token: token, ...extra });
        const res = await fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, body });
        const data = await res.json();
        alert(data.msg || '完成');
        if (data.code === 1) tableApi.reload();
    };
    document.getElementById('btn-approve')?.addEventListener('click', () => postAction(`${base}/approve`));
    document.getElementById('btn-reject')?.addEventListener('click', () => postAction(`${base}/reject`));
    document.getElementById('btn-batch-special')?.addEventListener('click', () => {
        const special_id = document.getElementById('batch-special')?.value;
        if (!special_id) return alert('请选择专题');
        postAction(`${base}/batch-special`, { special_id });
    });
    document.getElementById('btn-batch-tags')?.addEventListener('click', () => {
        const tag_names = document.getElementById('batch-tags')?.value?.trim();
        if (!tag_names) return alert('请输入标签');
        postAction(`${base}/batch-tags`, { tag_names });
    });
    @endif
})();
</script>
@endpush