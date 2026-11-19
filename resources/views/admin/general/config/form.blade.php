@extends('admin.layouts.app')

@section('title', $title)

@section('content')
<div class="card cms-card">
    <div class="card-body p-4">
        <form id="config-form" method="post" action="{{ $action }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">变量名 <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required maxlength="50" pattern="[a-z0-9_]+"
                           value="{{ old('name', $config->name) }}" @readonly($config->exists)>
                    <div class="form-text">小写字母、数字、下划线；编辑时不可修改</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">分组 <span class="text-danger">*</span></label>
                    <input type="text" name="group" class="form-control" list="config-groups" required maxlength="30"
                           value="{{ old('group', $config->group) }}">
                    <datalist id="config-groups">
                        @foreach($groupOptions as $g)
                            <option value="{{ $g }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="col-md-6">
                    <label class="form-label">标题 <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required maxlength="50"
                           value="{{ old('title', $config->title) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">类型</label>
                    <select name="type" class="form-select" id="cfg-type">
                        @foreach($typeOptions as $val => $lab)
                            <option value="{{ $val }}" @selected(old('type', $config->type) === $val)>{{ $lab }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">提示</label>
                    <input type="text" name="tip" class="form-control" maxlength="100"
                           value="{{ old('tip', $config->tip) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">可见条件</label>
                    <input type="text" name="visible" class="form-control" maxlength="255"
                           value="{{ old('visible', $config->visible) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">配置值</label>
                    <textarea name="value" class="form-control" rows="3" id="cfg-value">{{ old('value', $config->value) }}</textarea>
                    <div class="form-text" id="cfg-value-hint">当前类型的运行时取值</div>
                </div>
                <div class="col-12" id="cfg-content-wrap">
                    <label class="form-label">扩展数据 / 选项列表</label>
                    <textarea name="content" class="form-control font-monospace" rows="5" id="cfg-content"
                              placeholder="字典类型每行一项，如：&#10;normal|正常&#10;hidden|隐藏">{{ old('content', $config->content) }}</textarea>
                    <div class="form-text">类型为 array、select、radio 等时使用；每行 <code>key|说明</code> 或 <code>key:说明</code></div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">验证规则</label>
                    <input type="text" name="rule" class="form-control" maxlength="100"
                           value="{{ old('rule', $config->rule) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">扩展属性</label>
                    <input type="text" name="extend" class="form-control" maxlength="255"
                           value="{{ old('extend', $config->extend) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">设置</label>
                    <input type="text" name="setting" class="form-control" maxlength="255"
                           value="{{ old('setting', $config->setting) }}">
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">保存</button>
                <a href="{{ $backUrl }}" class="btn btn-secondary">返回</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const typeEl = document.getElementById('cfg-type');
    const contentWrap = document.getElementById('cfg-content-wrap');
    const hint = document.getElementById('cfg-value-hint');
    const dictTypes = ['array', 'select', 'checkbox', 'radio'];

    function syncType() {
        const t = typeEl.value;
        const showContent = dictTypes.includes(t);
        contentWrap.style.display = showContent ? '' : 'none';
        hint.textContent = showContent
            ? '一般为默认选中项 key，或 JSON 结构（部分类型）'
            : (t === 'text' ? '多行文本内容' : '配置项当前值');
    }
    typeEl.addEventListener('change', syncType);
    syncType();

    document.getElementById('config-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const res = await fetch(e.target.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: new FormData(e.target),
        });
        const data = await res.json();
        if (data.errors) {
            alert(Object.values(data.errors).flat().join('\n'));
            return;
        }
        alert(data.msg || '完成');
        if (data.code === 1) window.location.href = @json($backUrl);
    });
})();
</script>
@endpush