<?php

namespace App\Services\Cms\Payment;

use App\Models\Admin\Order;
use App\Models\Admin\UserMoneyLog;
use App\Models\Api\Member;

class BalancePaymentDriver implements PaymentDriver
{
    public function name(): string
    {
        return 'balance';
    }

    public function pay(Order $order): array
    {
        if ($order->status === 'paid') {
            return ['ok' => true, 'msg' => '已支付'];
        }

        $member = Member::query()->whereKey($order->member_id)->lockForUpdate()->first();
        if (! $member) {
            return ['ok' => false, 'msg' => '会员不存在'];
        }

        $amount = round((float) $order->amount, 2);
        $before = round((float) $member->money, 2);
        if ($before < $amount) {
            return ['ok' => false, 'msg' => '余额不足，请先充值'];
        }

        $after = round($before - $amount, 2);
        $member->money = $after;
        $member->save();

        UserMoneyLog::query()->create([
            'member_id' => $member->id,
            'money' => -$amount,
            'before' => $before,
            'after' => $after,
            'memo' => '订单 '.$order->order_sn,
            'createtime' => time(),
        ]);

        $order->status = 'paid';
        $order->pay_method = 'balance';
        $order->paytime = time();
        $order->updatetime = time();
        $order->save();

        return ['ok' => true, 'msg' => '支付成功'];
    }
}
