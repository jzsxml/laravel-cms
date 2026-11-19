@extends('member.layouts.app')

@section('title', '首页')

@section('content')
<div class="row g-3">
    <div class="col-md-8">
        <div class="card cms-member-card">
            <div class="card-body">
                <h2 class="h5 mb-3">欢迎，{{ $member->nickname ?: $member->username }}</h2>
                <dl class="row small mb-0">
                    <dt class="col-sm-3 text-muted">用户名</dt>
                    <dd class="col-sm-9">{{ $member->username }}</dd>
                    <dt class="col-sm-3 text-muted">邮箱</dt>
                    <dd class="col-sm-9">{{ $member->email ?: '—' }}</dd>
                    <dt class="col-sm-3 text-muted">手机</dt>
                    <dd class="col-sm-9">{{ $member->mobile ?: '—' }}</dd>
                    <dt class="col-sm-3 text-muted">上次登录</dt>
                    <dd class="col-sm-9">{{ $member->logintime ? date('Y-m-d H:i', $member->logintime) : '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card cms-member-card">
            <div class="card-body">
                <h3 class="h6 text-muted">快捷入口</h3>
                <a href="{{ route('member.profile') }}" class="btn btn-outline-success btn-sm w-100 mb-2">编辑资料</a>
                <a href="{{ route('member.contribute') }}" class="btn btn-success btn-sm w-100 mb-2">投稿</a>
                <a href="{{ route('member.wallet') }}" class="btn btn-outline-primary btn-sm w-100 mb-2">钱包 / 充值</a>
                <a href="{{ route('portal.member', $member->username) }}" class="btn btn-outline-secondary btn-sm w-100 mb-2" target="_blank" rel="noopener">我的公开主页</a>
                <p class="small text-muted mb-0">移动端 API 请使用 Header <code>X-Cms-Token</code>，与会话中的 token 一致。</p>
            </div>
        </div>
    </div>
</div>
@endsection