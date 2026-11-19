@extends('admin.layouts.app')

@section('title', '在线命令')

@section('content')
<div class="alert alert-warning py-2 small">
    仅可执行白名单内的只读/本地命令，禁止任意 Shell。
</div>
<div class="card cms-card">
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">命令</label>
            <select id="command" class="form-select" style="max-width:320px">
                @foreach($whitelist as $cmd)
                    <option value="{{ $cmd }}">{{ $cmd }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" class="btn btn-primary" id="run">执行</button>
        <pre class="bg-dark text-light p-3 mt-3 small" id="output" style="min-height:200px;max-height:50vh;overflow:auto"></pre>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('run').addEventListener('click', async () => {
    const command = document.getElementById('command').value;
    const body = new URLSearchParams({
        command,
        _token: document.querySelector('meta[name=csrf-token]').content,
    });
    const res = await fetch(@json(route('admin.general.command.run')), {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body,
    });
    const data = await res.json();
    document.getElementById('output').textContent = data.data?.output || data.msg || '';
});
</script>
@endpush