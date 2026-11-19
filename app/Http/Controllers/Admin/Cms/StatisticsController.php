<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Admin\Controller;
use App\Services\Cms\CmsStatistics;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    public function index(CmsStatistics $stats): View
    {
        $data = $stats->snapshot();
        $maxViews = max(1, ...array_column($data['views_trend'], 'count'));
        $maxOrders = max(1, ...array_column($data['orders_trend'], 'count'));

        return view('admin.cms.statistics', [
            'title' => '内容统计',
            'data' => $data,
            'maxViews' => $maxViews,
            'maxOrders' => $maxOrders,
        ]);
    }
}
