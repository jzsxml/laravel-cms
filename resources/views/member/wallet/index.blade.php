@extends('member.layouts.app')

@section('title', '钱包')

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card cms-member-card">
            <div class="card-body">
                <h2 class="h6 text-muted">账户余额</h2>
                <p class="display-6 fw-semibold text-success mb-2">¥{{ number_format((float) $member->money, 2) }}</p>
                @if(!empty($member->vip_expiretime) && (int) $member->vip_expiretime > time())
                    <p class="small mb-0 text-success">VIP 至 {{ date('Y-m-d', (int) $member->vip_expiretime) }}</p>
                @else
                    <p class="small text-muted mb-0">未开通 VIP</p>
                @endif
            </div>
        </div>
        <div class="card cms-member-card mt-3">
            <div class="card-header bg-white fw-semibold">模拟充值</div>
            <div class="card-body">
                <form method="post" action="{{ route('member.wallet.recharge') }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small">金额（元）</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" value="{{ config('cms.payment.mock_recharge_amount', 100) }}">
                    </div>
                    <button type="submit" class="btn btn-success btn-sm">充值到账</button>
                </form>
                <p class="small text-muted mt-2 mb-0">开发环境模拟入账，非真实支付。</p>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card cms-member-card mb-3">
            <div class="card-header bg-white fw-semibold">最近订单</div>
            <ul class="list-group list-group-flush">
                @forelse($orders as $o)
                    <li class="list-group-item d-flex justify-content-between small">
                        <span>{{ $o->title }} <span class="text-muted">({{ $o->order_sn }})</span></span>
                        <span>¥{{ number_format((float) $o->amount, 2) }} · {{ $o->status === 'paid' ? '已付' : '待付' }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">暂无订单</li>
                @endforelse
            </ul>
        </div>
        <div class="card cms-member-card">
            <div class="card-header bg-white fw-semibold">余额明细</div>
            <ul class="list-group list-group-flush">
                @forelse($logs as $log)
                    <li class="list-group-item d-flex justify-content-between small">
                        <span>{{ $log->memo }}</span>
                        <span class="{{ $log->money >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $log->money >= 0 ? '+' : '' }}{{ number_format((float) $log->money, 2) }}
                        </span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">暂无记录</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection