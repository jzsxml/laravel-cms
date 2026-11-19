@extends('admin.layouts.app')

@section('title', '控制台')

@section('content')
<div class="row g-3 mb-4">
    @php
        $cards = [
            ['label' => '管理员', 'value' => $stats['admins'] ?? 0, 'icon' => 'fa-user-shield', 'gradient' => '#3c8dbc,#00c0ef', 'href' => url(config('cms.admin_path').'/auth/admin')],
            ['label' => '前台会员', 'value' => $stats['members'] ?? 0, 'icon' => 'fa-users', 'gradient' => '#00a65a,#39d98a', 'href' => url(config('cms.admin_path').'/general/member')],
            ['label' => '附件', 'value' => $stats['attachments'] ?? 0, 'icon' => 'fa-paperclip', 'gradient' => '#f39c12,#f1c40f', 'href' => url(config('cms.admin_path').'/general/attachment')],
            ['label' => '今日登录', 'value' => $stats['logins_today'] ?? 0, 'icon' => 'fa-sign-in-alt', 'gradient' => '#605ca8,#9b59b6', 'href' => url(config('cms.admin_path').'/general/adminlog')],
        ];
    @endphp
    @foreach($cards as $card)
        <div class="col-sm-6 col-xl-3">
            <a href="{{ $card['href'] }}" class="text-decoration-none">
                <div class="card cms-stat h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="cms-stat-icon" style="background: linear-gradient(135deg, {{ $card['gradient'] }});">
                            <i class="fas {{ $card['icon'] }}"></i>
                        </div>
                        <div>
                            <div class="text-muted small">{{ $card['label'] }}</div>
                            <div class="fw-semibold fs-4">{{ number_format($card['value']) }}</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card cms-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-chart-bar me-1"></i> 近 7 日管理员登录</span>
                <span class="small text-muted">操作日志今日 {{ $stats['logs_today'] ?? 0 }} 条</span>
            </div>
            <div class="card-body">
                <div class="cms-mini-chart d-flex align-items-end gap-2" style="height:140px">
                    @foreach($loginTrend ?? [] as $bar)
                        @php $h = round(100 * ($bar['count'] / ($maxLogin ?? 1))); @endphp
                        <div class="flex-fill text-center">
                            <div class="cms-bar rounded-top mx-auto" style="height:{{ max(4, $h) }}%;max-width:48px;background:linear-gradient(180deg,var(--cms-primary),var(--cms-primary-dark));" title="{{ $bar['count'] }} 次"></div>
                            <div class="small text-muted mt-1">{{ $bar['label'] }}</div>
                            <div class="small fw-semibold">{{ $bar['count'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card cms-card h-100">
            <div class="card-header">内容概览</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between"><span>分类</span><strong>{{ $stats['categories'] ?? 0 }}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span>测试数据</span><strong>{{ $stats['tests'] ?? 0 }}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span>Laravel</span><strong class="small">{{ app()->version() }}</strong></li>
            </ul>
        </div>
    </div>
</div>

<div class="card cms-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>最近操作日志</span>
        <a href="{{ url(config('cms.admin_path').'/general/adminlog') }}" class="small">查看全部</a>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead><tr><th>用户</th><th>标题</th><th>IP</th><th>时间</th></tr></thead>
            <tbody>
            @forelse($recentLogs ?? [] as $log)
                <tr>
                    <td>{{ $log->username }}</td>
                    <td>{{ $log->title }}</td>
                    <td class="small text-muted">{{ $log->ip }}</td>
                    <td class="small text-muted">{{ $log->createtime ? date('Y-m-d H:i', (int) $log->createtime) : '' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-3">暂无日志</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
