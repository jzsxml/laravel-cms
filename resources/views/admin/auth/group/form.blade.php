@extends('admin.layouts.app')

@section('title', $title)

@section('content')
<div class="card cms-card">
    <div class="card-body p-4">
        <form id="group-form" method="post" action="{{ $action }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">名称 <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required maxlength="100"
                           value="{{ old('name', $group->name) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">父级</label>
                    <select name="pid" class="form-select">
                        <option value="0">无</option>
                        @foreach($parentOptions as $id => $name)
                            <option value="{{ $id }}" @selected((int) old('pid', $group->pid) === (int) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">状态</label>
                    <select name="status" class="form-select">
                        <option value="normal" @selected(old('status', $group->status) === 'normal')>正常</option>
                        <option value="hidden" @selected(old('status', $group->status) === 'hidden')>隐藏</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <label class="form-label mb-0">权限规则</label>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-check-visible">全选规则</button>
                    </div>
                </div>
                <div class="border rounded p-3 bg-light" style="max-height:360px;overflow:auto" id="rule-tree">
                    <label class="d-flex align-items-center gap-2 mb-3 fw-semibold">
                        <input type="checkbox" class="form-check-input m-0" id="rules-all" name="rules_all" value="1"
                               @checked($rulesAll) @disabled($rulesAll && $group->exists)>
                        全部权限（*）
                    </label>
                    <div id="rule-list" @class(['opacity-50' => $rulesAll])>
                        @include('admin.auth.group.partials.rule-node', [
                            'nodes' => $ruleTree,
                            'depth' => 0,
                            'selected' => $selectedRuleIds,
                        ])
                    </div>
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
    const form = document.getElementById('group-form');
    const rulesAll = document.getElementById('rules-all');
    const ruleList = document.getElementById('rule-list');

    function syncRulesAll() {
        const on = rulesAll.checked;
        ruleList.classList.toggle('opacity-50', on);
        ruleList.querySelectorAll('.rule-check').forEach((el) => {
            el.disabled = on;
            if (on) el.checked = false;
        });
    }

    rulesAll?.addEventListener('change', syncRulesAll);
    syncRulesAll();

    document.getElementById('btn-check-visible')?.addEventListener('click', () => {
        if (rulesAll.checked) return;
        ruleList.querySelectorAll('.rule-check:not(:disabled)').forEach((el) => { el.checked = true; });
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const res = await fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: new FormData(form),
        });
        const data = await res.json();
        alert(data.msg || '完成');
        if (data.code === 1) window.location.href = @json($backUrl);
    });
})();
</script>
@endpush