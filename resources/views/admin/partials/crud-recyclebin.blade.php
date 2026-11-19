@extends('admin.layouts.app')

@section('title', $title)

@section('content')
@include('admin.partials.cms-adv-filters', ['filterFields' => $filterFields ?? []])

<div class="cms-toolbar cms-toolbar-primary d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <a href="{{ url(config('cms.admin_path').'/'.$urlPrefix) }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left me-1"></i>{{ __('admin.ui.back_list') }}</a>
        @if(!empty($filterFields))
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#cms-adv-filter"><i class="fas fa-sliders-h me-1"></i>{{ __('admin.ui.filter') }}</button>
        @endif
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-refresh"><i class="fas fa-sync-alt" id="btn-refresh-icon"></i></button>
    </div>
    <button type="button" class="btn btn-sm btn-outline-danger" id="btn-destroy-batch"><i class="fas fa-fire me-1"></i>批量销毁</button>
</div>

<div class="cms-table-wrap cms-table-host" id="cms-table-host">
    <div class="cms-table-loading d-none" id="cms-table-loading"><div class="cms-table-spinner"></div><span>{{ __('admin.ui.loading') }}</span></div>
    <div class="table-responsive cms-table-scroll">
        <table class="table table-hover table-sm mb-0 cms-data-table">
            <thead>
            <tr>
                <th class="col-check"><input type="checkbox" id="check-all" class="form-check-input"></th>
                @foreach($listFields as $f)
                    <th class="cms-sort-th" data-sort-field="{{ $f }}"><span>{{ $listFieldLabels[$f] ?? $f }}</span><i class="fas fa-sort cms-sort-icon text-muted"></i></th>
                @endforeach
                <th>{{ __('admin.ui.operations') }}</th>
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
                @foreach([10, 15, 20, 50] as $ps)
                    <option value="{{ $ps }}" @selected($ps === 10)>{{ $ps }}</option>
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
    const listBase = @json(url(config('cms.admin_path').'/'.$urlPrefix));
    const fetchBase = `${listBase}/recyclebin`;
    const listFields = @json($listFields);
    const token = document.querySelector('meta[name=csrf-token]').content;

    function rowHtml(row) {
        const cells = listFields.map(f => `<td>${row[f] ?? ''}</td>`).join('');
        return `<tr class="cms-table-row">
            <td class="col-check"><input type="checkbox" class="form-check-input row-check" value="${row.id}"></td>
            ${cells}
            <td class="text-nowrap">
                <button type="button" class="btn btn-xs btn-soft-success" data-restore="${row.id}"><i class="fas fa-undo"></i> 还原</button>
                <button type="button" class="btn btn-xs btn-soft-danger" data-destroy="${row.id}"><i class="fas fa-fire"></i></button>
            </td>
        </tr>`;
    }

    const tableApi = CmsTable.init({
        base: fetchBase,
        rowHtml,
        listFields,
        onBind(tbody) {
            tbody.querySelectorAll('[data-restore]').forEach(btn => {
                btn.onclick = async () => {
                    if (!confirm('确认还原？')) return;
                    const body = new URLSearchParams({ ids: btn.getAttribute('data-restore'), _token: token });
                    const res = await fetch(`${listBase}/restore`, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body });
                    const data = await res.json();
                    alert(data.msg || '完成');
                    if (data.code === 1) tableApi.reload();
                };
            });
            tbody.querySelectorAll('[data-destroy]').forEach(btn => {
                btn.onclick = async () => {
                    if (!confirm('永久删除且不可恢复，确认？')) return;
                    const body = new URLSearchParams({ ids: btn.getAttribute('data-destroy'), _token: token });
                    const res = await fetch(`${listBase}/destroy`, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body });
                    const data = await res.json();
                    alert(data.msg || '完成');
                    if (data.code === 1) tableApi.reload();
                };
            });
        },
    });

    document.getElementById('btn-destroy-batch')?.addEventListener('click', async () => {
        const ids = [...document.querySelectorAll('.row-check:checked')].map(c => c.value);
        if (!ids.length) return alert('请先勾选记录');
        if (!confirm('永久删除所选记录？')) return;
        const body = new URLSearchParams({ ids: ids.join(','), _token: token });
        const res = await fetch(`${listBase}/destroy`, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body });
        const data = await res.json();
        alert(data.msg || '完成');
        if (data.code === 1) tableApi.reload();
    });
})();
</script>
@endpush