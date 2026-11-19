@extends('admin.layouts.app')

@section('title', $title)

@section('content')
<div class="cms-toolbar mb-3">
    <form id="upload-form" enctype="multipart/form-data">
        @csrf
        <p class="small text-muted mb-2">拖曳文件到下方区域可上传；浏览器不支持的类型用选择文件</p>
        <div id="upload-drop" class="cms-upload-drop border border-2 border-dashed rounded p-4 text-center mb-2" tabindex="0">
            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2 d-block"></i>
            <span class="text-muted">拖曳文件到此处上传</span>
            <input type="file" name="file" id="upload-file" class="d-none" required>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-pick-file">选择文件</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-upload me-1"></i>上传附件</button>
            <span id="upload-msg" class="small text-muted"></span>
        </div>
    </form>
</div>

@include('admin.partials.cms-adv-filters', ['filterFields' => $filterFields ?? []])

<div class="cms-toolbar cms-toolbar-primary d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="d-flex gap-2 align-items-center">
        <div class="input-group input-group-sm cms-search-group">
            <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
            <input type="search" class="form-control" id="search" placeholder="快速搜索…" style="max-width:220px">
            <button class="btn btn-primary" type="button" id="btn-search">搜索</button>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#cms-adv-filter"><i class="fas fa-sliders-h"></i></button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-refresh"><i class="fas fa-sync-alt" id="btn-refresh-icon"></i></button>
    </div>
</div>

<div class="cms-table-wrap cms-table-host" id="cms-table-host">
    <div class="cms-table-loading d-none" id="cms-table-loading"><div class="cms-table-spinner"></div><span>加载中…</span></div>
    <div class="table-responsive cms-table-scroll">
        <table class="table table-hover table-sm mb-0 cms-data-table">
            <thead>
            <tr>
                <th class="col-check"><input type="checkbox" id="check-all" class="form-check-input"></th>
                @foreach($listFields as $f)
                    <th class="cms-sort-th" data-sort-field="{{ $f }}">{{ $listFieldLabels[$f] ?? $f }}</th>
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
            <select id="page-size" class="form-select form-select-sm" style="width:auto">
                @foreach([10, 15, 20, 50] as $ps)
                    <option value="{{ $ps }}">{{ $ps }}</option>
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
    const token = document.querySelector('meta[name=csrf-token]').content;

    function rowHtml(row) {
        const cells = listFields.map(f => {
            if (f === 'url' && row.url) {
                return `<td><a href="${row.url}" target="_blank" rel="noopener" class="small">${row.url}</a></td>`;
            }
            return `<td>${row[f] ?? ''}</td>`;
        }).join('');
        return `<tr class="cms-table-row">
            <td class="col-check"><input type="checkbox" class="form-check-input row-check" value="${row.id}"></td>
            ${cells}
            <td><button type="button" class="btn btn-xs btn-soft-danger" data-del="${row.id}"><i class="fas fa-trash-alt"></i></button></td>
        </tr>`;
    }

    const tableApi = CmsTable.init({
        base,
        rowHtml,
        listFields,
        onBind(tbody) {
            tbody.querySelectorAll('[data-del]').forEach(btn => {
                btn.onclick = async () => {
                    if (!confirm('确认删除？将同时移除存储文件。')) return;
                    const body = new URLSearchParams({ ids: btn.getAttribute('data-del'), _token: token });
                    const res = await fetch(`${base}/del`, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body });
                    const data = await res.json();
                    alert(data.msg || '完成');
                    if (data.code === 1) tableApi.reload();
                };
            });
        },
    });
    window.__attachmentTable = tableApi;

    const uploadForm = document.getElementById('upload-form');
    const uploadFile = document.getElementById('upload-file');
    const uploadDrop = document.getElementById('upload-drop');
    const uploadMsg = document.getElementById('upload-msg');

    async function doUpload(fd) {
        const res = await fetch(@json($uploadUrl), {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: fd,
        });
        const data = await res.json();
        uploadMsg.textContent = data.msg || '';
        if (data.code === 1) {
            uploadForm.reset();
            uploadFile.value = '';
            tableApi.reload();
        }
    }

    uploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!uploadFile.files?.length) {
            uploadMsg.textContent = '请选择文件';
            return;
        }
        await doUpload(new FormData(uploadForm));
    });

    document.getElementById('btn-pick-file')?.addEventListener('click', () => uploadFile.click());
    uploadFile.addEventListener('change', () => {
        if (uploadFile.files?.length) uploadMsg.textContent = uploadFile.files[0].name;
    });

    ['dragenter', 'dragover'].forEach((ev) => {
        uploadDrop.addEventListener(ev, (e) => {
            e.preventDefault();
            uploadDrop.classList.add('bg-light');
        });
    });
    ['dragleave', 'drop'].forEach((ev) => {
        uploadDrop.addEventListener(ev, (e) => {
            e.preventDefault();
            uploadDrop.classList.remove('bg-light');
        });
    });
    uploadDrop.addEventListener('drop', async (e) => {
        const file = e.dataTransfer?.files?.[0];
        if (!file) return;
        const dt = new DataTransfer();
        dt.items.add(file);
        uploadFile.files = dt.files;
        uploadMsg.textContent = file.name;
        const fd = new FormData(uploadForm);
        await doUpload(fd);
    });
})();
</script>
@endpush