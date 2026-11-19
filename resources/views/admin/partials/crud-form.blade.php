@extends('admin.layouts.app')

@section('title', $title)

@section('content')
<div class="card cms-card">
    <div class="card-body p-4">
        <form id="crud-form" method="post" action="{{ $action }}" enctype="multipart/form-data">
            @csrf
            @php
                $extras = $formExtras ?? [];
                $uploadUrl = route('admin.ajax.upload');
                $selectpageUrl = route('admin.ajax.selectpage');
            @endphp
            @foreach($formFields as $name => $meta)
                @php
                    [$type, $label] = array_pad(explode(':', $meta, 2), 2, $name);
                    $label = $label ?: $name;
                    $val = old($name, $record->{$name} ?? '');
                @endphp
                <div class="mb-3" data-field="{{ $name }}" data-type="{{ $type }}">
                    <label class="form-label">{{ $label }}</label>
                    @if($type === 'editor')
                        <textarea name="{{ $name }}" class="form-control summernote" rows="8">{{ $val }}</textarea>
                    @elseif($type === 'textarea')
                        <textarea name="{{ $name }}" class="form-control" rows="4">{{ $val }}</textarea>
                    @elseif($type === 'switch')
                        <input type="hidden" name="{{ $name }}" value="0">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="{{ $name }}" value="1" id="f_{{ $name }}" @checked(old($name, $record->{$name} ?? 0))>
                            <label class="form-check-label" for="f_{{ $name }}">开启</label>
                        </div>
                    @elseif($type === 'select')
                        @php
                            $options = match($name) {
                                'status' => $extras['statusOptions'] ?? ['normal' => '正常', 'hidden' => '隐藏'],
                                'pay_type' => $extras['payTypeOptions'] ?? ['free' => '免费', 'pay' => '付费'],
                                'type' => $extras['typeOptions'] ?? [],
                                'template' => $extras['templateOptions'] ?? ['default' => '默认'],
                                default => $extras[$name.'Options'] ?? [],
                            };
                        @endphp
                        <select name="{{ $name }}" class="form-select">
                            @foreach($options as $k => $optLabel)
                                <option value="{{ $k }}" @selected((string)$val === (string)$k)>{{ $optLabel }}</option>
                            @endforeach
                        </select>
                    @elseif($type === 'selectpage')
                        @php
                            $spSource = match ($name) {
                                'admin_id' => 'admin',
                                'category_id', 'pid' => 'category',
                                default => 'category',
                            };
                            $spType = $extras['categorySelectType'] ?? (($name === 'category_id') ? 'test' : '');
                        @endphp
                        <div class="input-group selectpage-wrap">
                            <input type="number" name="{{ $name }}" class="form-control selectpage-id" value="{{ $val }}"
                                   data-source="{{ $spSource }}" @if($spType) data-custom-type="{{ $spType }}" @endif>
                            <button type="button" class="btn btn-outline-secondary btn-selectpage">选择</button>
                        </div>
                        <div class="small text-muted selectpage-label mt-1"></div>
                    @elseif($type === 'image')
                        <div class="d-flex gap-2 align-items-center">
                            <input type="text" name="{{ $name }}" class="form-control image-url" value="{{ $val }}">
                            <input type="file" class="d-none image-file" accept="image/*">
                            <button type="button" class="btn btn-outline-primary btn-upload-image">上传</button>
                        </div>
                        @if($val)
                            <img src="{{ $val }}" class="mt-2 rounded border" style="max-height:80px" alt="">
                        @endif
                    @elseif($type === 'images')
                        <span class="d-flex gap-2 align-items-center flex-wrap">
                            <input type="text" name="{{ $name }}" class="form-control images-url" value="{{ $val }}" placeholder="多图逗号分隔">
                            <input type="file" class="d-none images-file" accept="image/*" multiple>
                            <button type="button" class="btn btn-outline-primary btn-upload-images">上传多图</button>
                        </span>
                    @elseif($type === 'file')
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <input type="text" name="{{ $name }}" class="form-control file-url" value="{{ $val }}">
                            <input type="file" class="d-none file-chunk-input">
                            <button type="button" class="btn btn-outline-primary btn-chunk-upload">分片上传</button>
                        </div>
                    @elseif($type === 'number')
                        <input type="number" name="{{ $name }}" class="form-control" value="{{ $val }}">
                    @else
                        <input type="text" name="{{ $name }}" class="form-control" value="{{ $val }}">
                    @endif
                    @if($name === 'slug' && !empty($extras['slugHint']))
                        <div class="form-text">{{ $extras['slugHint'] }}</div>
                    @endif
                    @if($name === 'extra_category_ids' && !empty($extras['extraCategoryHint']))
                        <div class="form-text">{{ $extras['extraCategoryHint'] }}</div>
                    @endif
                    @if($name === 'tag_names' && !empty($extras['tagHint']))
                        <div class="form-text">{{ $extras['tagHint'] }}</div>
                    @endif
                    @if($name === 'flag' && !empty($extras['flagHint']))
                        <div class="form-text">{{ $extras['flagHint'] }}</div>
                    @endif
                    @if($name === 'pay_type' && !empty($extras['payHint']))
                        <div class="form-text">{{ $extras['payHint'] }}</div>
                    @endif
                    @if($name === 'fields' && !empty($extras['fieldsHint']))
                        <div class="form-text"><code>{{ $extras['fieldsHint'] }}</code></div>
                    @endif
                    @if($name === 'name' && !empty($extras['nameHint']))
                        <div class="form-text">{{ $extras['nameHint'] }}</div>
                    @endif
                    @if($name === 'archive_ids' && !empty($extras['archiveIdsHint']))
                        <div class="form-text">{{ $extras['archiveIdsHint'] }}</div>
                    @endif
                </div>
            @endforeach
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">保存</button>
                <a href="{{ $backUrl }}" class="btn btn-secondary">返回</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script>
if (window.jQuery && jQuery.fn.summernote) {
    jQuery('.summernote').summernote({ height: 220, lang: 'zh-CN' });
}
</script>
<script>
document.getElementById('crud-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    if (window.jQuery) {
        jQuery('.summernote').each(function () {
            const markup = jQuery(this).summernote('code');
            jQuery(this).val(markup);
        });
    }
    const res = await fetch(e.target.action, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: new FormData(e.target),
    });
    const data = await res.json();
    alert(data.msg || '完成');
    if (data.code === 1) window.location.href = e.target.querySelector('a.btn-secondary').href;
});

const uploadUrl = @json($uploadUrl);
const chunkUrl = @json(route('admin.ajax.uploadchunk'));
const mergeUrl = @json(route('admin.ajax.uploadmerge'));
const selectpageUrl = @json($selectpageUrl);
const token = document.querySelector('meta[name=csrf-token]').content;
const chunkSize = {{ (int) config('cms.upload.chunk_size', 2097152) }};

document.querySelectorAll('.btn-upload-image').forEach(btn => {
    btn.addEventListener('click', () => {
        const wrap = btn.closest('[data-field]');
        const fileInput = wrap.querySelector('.image-file');
        fileInput.onchange = async () => {
            if (!fileInput.files.length) return;
            const fd = new FormData();
            fd.append('file', fileInput.files[0]);
            fd.append('_token', token);
            const res = await fetch(uploadUrl, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
            const data = await res.json();
            if (data.code === 1) wrap.querySelector('.image-url').value = data.data.url;
        };
        fileInput.click();
    });
});

document.querySelectorAll('.btn-selectpage').forEach(btn => {
    btn.addEventListener('click', async () => {
        const wrap = btn.closest('.selectpage-wrap');
        const input = wrap.querySelector('.selectpage-id');
        const q = prompt('搜索关键词（留空列出）', '') ?? '';
        const source = input.dataset.source || 'category';
        const params = new URLSearchParams({ source, q, pageSize: 20 });
        if (input.dataset.customType) {
            params.set('type', input.dataset.customType);
        }
        const res = await fetch(`${selectpageUrl}?${params}`, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        const list = data.list || [];
        if (!list.length) return alert('无匹配数据');
        const lines = list.map((row, i) => `${i + 1}. [${row.id}] ${row.name}`).join('\n');
        const pick = prompt(`输入序号选择：\n${lines}`, '1');
        const idx = parseInt(pick, 10) - 1;
        if (list[idx]) {
            input.value = list[idx].id;
            wrap.parentElement.querySelector('.selectpage-label').textContent = list[idx].name;
        }
    });
});

document.querySelectorAll('.btn-upload-images').forEach(btn => {
    btn.addEventListener('click', () => {
        const wrap = btn.closest('[data-field]');
        const fileInput = wrap.querySelector('.images-file');
        fileInput.onchange = async () => {
            const urls = [];
            for (const file of fileInput.files) {
                const fd = new FormData();
                fd.append('file', file);
                fd.append('_token', token);
                const res = await fetch(uploadUrl, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
                const data = await res.json();
                if (data.code === 1 && data.data?.url) urls.push(data.data.url);
            }
            if (urls.length) {
                const input = wrap.querySelector('.images-url');
                const prev = input.value ? input.value.split(',').map(s => s.trim()).filter(Boolean) : [];
                input.value = [...prev, ...urls].join(',');
            }
        };
        fileInput.click();
    });
});

async function hydrateSelectpageLabels() {
    for (const input of document.querySelectorAll('.selectpage-id')) {
        const id = parseInt(input.value, 10);
        if (!id) continue;
        const source = input.dataset.source || 'category';
        const params = new URLSearchParams({ source, id: String(id), pageSize: 1 });
        if (input.dataset.customType) params.set('type', input.dataset.customType);
        const res = await fetch(`${selectpageUrl}?${params}`, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        const row = (data.list || []).find(r => String(r.id) === String(id)) || data.list?.[0];
        if (row?.name) {
            input.closest('[data-field]')?.querySelector('.selectpage-label')?.replaceChildren(document.createTextNode(row.name));
        }
    }
}
hydrateSelectpageLabels();

document.querySelectorAll('.btn-chunk-upload').forEach(btn => {
    btn.addEventListener('click', () => {
        const wrap = btn.closest('[data-field]');
        const input = wrap.querySelector('.file-chunk-input');
        input.onchange = async () => {
            const file = input.files[0];
            if (!file) return;
            const chunkId = 'c' + Date.now();
            const count = Math.ceil(file.size / chunkSize);
            for (let i = 0; i < count; i++) {
                const blob = file.slice(i * chunkSize, Math.min(file.size, (i + 1) * chunkSize));
                const fd = new FormData();
                fd.append('chunkid', chunkId);
                fd.append('chunkindex', i);
                fd.append('chunkcount', count);
                fd.append('file', blob, file.name);
                fd.append('_token', token);
                const res = await fetch(chunkUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();
                if (data.code !== 1) return alert(data.msg || '分片失败');
            }
            const merge = new URLSearchParams({
                chunkid: chunkId,
                chunkcount: count,
                filename: file.name,
                _token: token,
            });
            const mres = await fetch(mergeUrl, { method: 'POST', body: merge, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const mdata = await mres.json();
            if (mdata.code === 1) wrap.querySelector('.file-url').value = mdata.data.url;
            alert(mdata.msg || '完成');
        };
        input.click();
    });
});
</script>
@endpush