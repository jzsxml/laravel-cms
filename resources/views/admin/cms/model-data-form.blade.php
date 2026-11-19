@extends('admin.layouts.app')

@section('title', $title)

@section('content')
<div class="card cms-card">
    <div class="card-body p-4">
        <form id="crud-form" method="post" action="{{ $action }}">
            @csrf
            @foreach($cmsModel->fieldSchema() as $field)
                <div class="mb-3">
                    <label class="form-label">{{ $field['label'] }}</label>
                    @if($field['type'] === 'textarea')
                        <textarea name="{{ $field['name'] }}" class="form-control" rows="4">{{ old($field['name'], $record->{$field['name']} ?? '') }}</textarea>
                    @elseif($field['type'] === 'number')
                        <input type="number" step="any" name="{{ $field['name'] }}" class="form-control" value="{{ old($field['name'], $record->{$field['name']} ?? '') }}">
                    @elseif($field['type'] === 'image')
                        <input type="text" name="{{ $field['name'] }}" class="form-control" value="{{ old($field['name'], $record->{$field['name']} ?? '') }}" placeholder="图片 URL">
                    @elseif($field['type'] === 'select' && !empty($field['options']))
                        <select name="{{ $field['name'] }}" class="form-select">
                            @foreach($field['options'] as $k => $label)
                                <option value="{{ $k }}" @selected((string) old($field['name'], $record->{$field['name']} ?? '') === (string) $k)>{{ $label }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" name="{{ $field['name'] }}" class="form-control" value="{{ old($field['name'], $record->{$field['name']} ?? '') }}">
                    @endif
                </div>
            @endforeach
            <div class="mb-3">
                <label class="form-label">状态</label>
                <select name="status" class="form-select">
                    <option value="normal" @selected(old('status', $record->status ?? 'normal') === 'normal')>正常</option>
                    <option value="hidden" @selected(old('status', $record->status ?? '') === 'hidden')>隐藏</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">权重</label>
                <input type="number" name="weigh" class="form-control" value="{{ old('weigh', $record->weigh ?? 0) }}">
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
            <a href="{{ $backUrl }}" class="btn btn-link">返回</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('crud-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const res = await fetch(e.target.action, { method: 'POST', body: new FormData(e.target), headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
    const data = await res.json();
    alert(data.msg || '完成');
    if (data.code === 1) location.href = @json($backUrl);
});
</script>
@endpush