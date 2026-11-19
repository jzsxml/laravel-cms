<?php

namespace App\Http\Controllers\Member;

use App\Models\Admin\Order;
use App\Models\Admin\UserMoneyLog;
use App\Services\Cms\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(
        protected OrderService $orders,
    ) {}

    public function index(): View
    {
        $member = $this->memberAuth->member();
        $logs = UserMoneyLog::query()
            ->where('member_id', $member->id)
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        $orders = Order::query()
            ->where('member_id', $member->id)
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return view('member.wallet.index', [
            'member' => $member,
            'logs' => $logs,
            'orders' => $orders,
        ]);
    }

    public function recharge(Request $request): RedirectResponse
    {
        $member = $this->memberAuth->member();
        $amount = (float) $request->input('amount', config('cms.payment.mock_recharge_amount', 100));
        $max = (float) config('cms.payment.mock_recharge_max', 1000);
        if ($amount <= 0 || $amount > $max) {
            return back()->with('error', '充值金额需在 0.01～'.$max.' 之间');
        }

        if (! config('cms.payment.allow_mock_recharge', true)) {
            return back()->with('error', '模拟充值未启用');
        }

        $this->orders->recharge($member, $amount, '模拟充值');

        return back()->with('success', '充值成功，当前余额 ¥'.number_format((float) $member->fresh()->money, 2));
    }
}
