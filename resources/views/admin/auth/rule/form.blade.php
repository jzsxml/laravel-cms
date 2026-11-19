@extends('admin.layouts.app')

@section('title', $title)

@section('content')
<div class="card cms-card">
    <div class="card-body p-4">
        <form id="rule-form" method="post" action="{{ $action }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">父级</label>
                    <select name="pid" class="form-select">
                        <option value="0">无（顶级）</option>
                        @foreach($parentOptions as $id => $label)
                            <option value="{{ $id }}" @selected((int) old('pid', $rule->pid) === (int) $id)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">类型</label>
                    <select name="type" class="form-select">
                        <option value="file" @selected(old('type', $rule->type) === 'file')>权限节点 (file)</option>
                        <option value="menu" @selected(old('type', $rule->type) === 'menu')>菜单目录 (menu)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">规则名称 <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required maxlength="100"
                           pattern="[a-z0-9_/]+"
                           placeholder="例如 general/config/index"
                           value="{{ old('name', $rule->name) }}">
                    <div class="form-text">小写字母、数字、下划线与斜杠，需与控制器权限名一致</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">标题 <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required maxlength="50"
                           value="{{ old('title', $rule->title) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">图标</label>
                    <input type="text" name="icon" class="form-control" maxlength="50" placeholder="fa fa-cog"
                           value="{{ old('icon', $rule->icon) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">URL</label>
                    <input type="text" name="url" class="form-control" maxlength="255"
                           value="{{ old('url', $rule->url) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">权重</label>
                    <input type="number" name="weigh" class="form-control" value="{{ old('weigh', $rule->weigh) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">状态</label>
                    <select name="status" class="form-select">
                        <option value="normal" @selected(old('status', $rule->status) === 'normal')>正常</option>
                        <option value="hidden" @selected(old('status', $rule->status) === 'hidden')>隐藏</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">菜单类型</label>
                    <select name="menutype" class="form-select" id="menutype">
                        <option value="">默认</option>
                        @foreach(['addtabs' => 'Addtabs', 'blank' => '新窗口', 'dialog' => '弹窗', 'ajax' => 'Ajax'] as $val => $lab)
                            <option value="{{ $val }}" @selected(old('menutype', $rule->menutype) === $val)>{{ $lab }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="ismenu" value="1" id="ismenu"
                               @checked((int) old('ismenu', $rule->ismenu) === 1)>
                        <label class="form-check-label" for="ismenu">显示在左侧菜单</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">备注</label>
                    <textarea name="remark" class="form-control" rows="2" maxlength="255">{{ old('remark', $rule->remark) }}</textarea>
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
    const form = document.getElementById('rule-form');
    const ismenu = document.getElementById('ismenu');
    const menutype = document.getElementById('menutype');

    function syncMenuType() {
        menutype.disabled = !ismenu.checked;
        if (!ismenu.checked) menutype.value = '';
    }
    ismenu.addEventListener('change', syncMenuType);
    syncMenuType();

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const res = await fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: new FormData(form),
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