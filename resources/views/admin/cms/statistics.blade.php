@extends('admin.layouts.app')

@section('title', $title)

@section('content')
<div class="row g-3 mb-4">
    @foreach([
        ['label' => '已发文章', 'value' => $data['archives_published'], 'icon' => 'fa-file-alt'],
        ['label' => '待审投稿', 'value' => $data['archives_pending'], 'icon' => 'fa-clock'],
        ['label' => '总阅读', 'value' => number_format($data['archives_views']), 'icon' => 'fa-eye'],
        ['label' => '评论', 'value' => $data['comments'], 'icon' => 'fa-comments'],
        ['label' => '会员', 'value' => $data['members'], 'icon' => 'fa-users'],
        ['label' => '已付订单', 'value' => $data['orders_paid'], 'icon' => 'fa-receipt'],
        ['label' => '交易额', 'value' => '¥'.number_format($data['orders_amount'], 2), 'icon' => 'fa-yen-sign'],
        ['label' => '近7日订单', 'value' => $data['orders_week'].' / ¥'.number_format($data['amount_week'], 2), 'icon' => 'fa-chart-line'],
    ] as $card)
        <div class="col-md-6 col-xl-3">
            <div class="card cms-card border-0 h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">{{ $card['label'] }}</div>
                        <div class="fs-5 fw-semibold">{{ $card['value'] }}</div>
                    </div>
                    <i class="fas {{ $card['icon'] }} fa-lg text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card cms-card border-0">
            <div class="card-header bg-white fw-semibold">近 7 日内容新增</div>
            <div class="card-body">
                <div class="d-flex align-items-end gap-1" style="height:120px">
                    @foreach($data['views_trend'] as $bar)
                        <div class="flex-grow-1 text-center" style="min-width:0">
                            <div class="bg-primary rounded-top mx-auto" style="width:80%;height:{{ max(4, (int) round(100 * $bar['count'] / $maxViews)) }}px" title="{{ $bar['count'] }}"></div>
                            <small class="text-muted d-block mt-1" style="font-size:10px">{{ $bar['label'] }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card cms-card border-0">
            <div class="card-header bg-white fw-semibold">近 7 日付费订单</div>
            <div class="card-body">
                <div class="d-flex align-items-end gap-1" style="height:120px">
                    @foreach($data['orders_trend'] as $bar)
                        <div class="flex-grow-1 text-center" style="min-width:0">
                            <div class="bg-success rounded-top mx-auto" style="width:80%;height:{{ max(4, (int) round(100 * $bar['count'] / $maxOrders)) }}px"></div>
                            <small class="text-muted d-block mt-1" style="font-size:10px">{{ $bar['label'] }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card cms-card border-0 mt-4">
    <div class="card-header bg-white fw-semibold">阅读排行</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>标题</th><th class="text-end">阅读</th></tr></thead>
            <tbody>
                @forelse($data['top_archives'] as $row)
                    <tr>
                        <td>{{ $row->title }}</td>
                        <td class="text-end">{{ $row->views }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="text-muted">暂无数据</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection