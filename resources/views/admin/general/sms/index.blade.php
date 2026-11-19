@extends('admin.layouts.app')

@section('title', $title)

@section('content')
<div class="card cms-card" style="max-width:520px">
    <div class="card-header">短信测试（当前驱动：{{ $driver }}）</div>
    <div class="card-body">
        <form id="sms-form">
            @csrf
            <div class="mb-3">
                <label class="form-label">手机号</label>
                <input type="text" name="mobile" class="form-control" required maxlength="20" placeholder="13800138000">
            </div>
            <div class="mb-3">
                <label class="form-label">内容</label>
                <textarea name="content" class="form-control" rows="3" required maxlength="500">【Laravel CMS】您的验证码是 123456</textarea>
            </div>
            <button type="submit" class="btn btn-primary">发送</button>
        </form>
        <p class="small text-muted mt-3 mb-0">驱动为 <code>log</code> 时写入 <code>storage/logs</code>。生产请配置 <code>CMS_SMS_DRIVER</code> 与阿里云密钥。</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('sms-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const res = await fetch(@json(url(config('cms.admin_path').'/general/sms/send')), {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: new FormData(e.target),
    });
    const data = await res.json();
    alert(data.msg || '完成');
});
</script>
@endpush