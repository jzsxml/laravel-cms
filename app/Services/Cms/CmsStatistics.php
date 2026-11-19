<?php

namespace App\Services\Cms;

use App\Models\Admin\Archive;
use App\Models\Admin\Comment;
use App\Models\Admin\Order;
use App\Models\Api\Member;
use App\Support\CmsTable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CmsStatistics
{
    /**
     * @return array<string, mixed>
     */
    public function snapshot(): array
    {
        $today = strtotime('today');
        $weekAgo = $today - 6 * 86400;

        $archiveBase = Archive::query()->whereNull('deletetime');
        $ordersPaid = Order::query()->where('status', 'paid');

        return [
            'archives_total' => (clone $archiveBase)->count(),
            'archives_published' => (clone $archiveBase)->where('status', 'normal')->count(),
            'archives_pending' => (clone $archiveBase)->where('status', 'pending')->count(),
            'archives_views' => (int) (clone $archiveBase)->sum('views'),
            'comments' => Comment::query()->whereNull('deletetime')->where('status', 'normal')->count(),
            'members' => Member::query()->where('status', 'normal')->count(),
            'orders_paid' => (clone $ordersPaid)->count(),
            'orders_amount' => round((float) (clone $ordersPaid)->sum('amount'), 2),
            'orders_week' => (clone $ordersPaid)->where('paytime', '>=', $weekAgo)->count(),
            'amount_week' => round((float) (clone $ordersPaid)->where('paytime', '>=', $weekAgo)->sum('amount'), 2),
            'views_trend' => $this->dailyCounts(CmsTable::name('archives'), 'createtime', $weekAgo, $today, fn ($q) => $q->whereNull('deletetime')),
            'orders_trend' => $this->dailyCounts(CmsTable::name('order'), 'paytime', $weekAgo, $today, fn ($q) => $q->where('status', 'paid')),
            'top_archives' => Archive::query()
                ->where('status', 'normal')
                ->whereNull('deletetime')
                ->orderByDesc('views')
                ->limit(8)
                ->get(['id', 'title', 'views', 'slug']),
        ];
    }

    /**
     * @param  callable(Builder): void|null  $modifier
     * @return list<array{label: string, count: int}>
     */
    protected function dailyCounts(string $table, string $timeCol, int $from, int $to, ?callable $modifier = null): array
    {
        $out = [];
        for ($d = $from; $d <= $to; $d += 86400) {
            $next = $d + 86400;
            $q = DB::table($table)->whereBetween($timeCol, [$d, $next - 1]);
            if ($modifier) {
                $modifier($q);
            }
            $out[] = ['label' => date('m-d', $d), 'count' => (int) $q->count()];
        }

        return $out;
    }
}
