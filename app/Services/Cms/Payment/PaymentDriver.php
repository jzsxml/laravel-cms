<?php

namespace App\Services\Cms\Payment;

use App\Models\Admin\Order;

interface PaymentDriver
{
    public function name(): string;

    /**
     * @return array{ok: bool, msg: string}
     */
    public function pay(Order $order): array;
}
