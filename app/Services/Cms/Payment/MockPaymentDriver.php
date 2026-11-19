<?php

namespace App\Services\Cms\Payment;

use App\Models\Admin\Order;

class MockPaymentDriver implements PaymentDriver
{
    public function name(): string
    {
        return 'mock';
    }

    public function pay(Order $order): array
    {
        if ($order->status === 'paid') {
            return ['ok' => true, 'msg' => '已支付'];
        }

        $order->status = 'paid';
        $order->pay_method = 'mock';
        $order->paytime = time();
        $order->updatetime = time();
        $order->save();

        return ['ok' => true, 'msg' => '模拟支付成功'];
    }
}
