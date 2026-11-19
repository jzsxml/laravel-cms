<?php

namespace App\Services\Cms;

use App\Models\Admin\Archive;
use App\Models\Admin\CmsPage;
use App\Models\Admin\Order;
use App\Models\Admin\UserMoneyLog;
use App\Models\Api\Member;
use App\Services\Cms\Payment\BalancePaymentDriver;
use App\Services\Cms\Payment\MockPaymentDriver;
use App\Services\Cms\Payment\PaymentDriver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected ContentAccess $contentAccess,
    ) {}

    public function driver(string $name): PaymentDriver
    {
        return match ($name) {
            'mock' => new MockPaymentDriver,
            default => new BalancePaymentDriver,
        };
    }

    public function createContentOrder(Member $member, Model $product): Order
    {
        $type = $product instanceof Archive ? 'archive' : 'page';
        if (! $this->contentAccess->isPaidContent($product)) {
            throw new \InvalidArgumentException('该内容无需付费');
        }

        $existing = Order::query()
            ->where('member_id', $member->id)
            ->where('product_type', $type)
            ->where('product_id', $product->getKey())
            ->where('status', 'paid')
            ->first();
        if ($existing) {
            return $existing;
        }

        $open = Order::query()
            ->where('member_id', $member->id)
            ->where('product_type', $type)
            ->where('product_id', $product->getKey())
            ->where('status', 'created')
            ->first();
        if ($open) {
            return $open;
        }

        $now = time();

        return Order::query()->create([
            'order_sn' => Order::generateSn(),
            'member_id' => $member->id,
            'product_type' => $type,
            'product_id' => (int) $product->getKey(),
            'title' => (string) $product->title,
            'amount' => round((float) $product->price, 2),
            'pay_method' => 'balance',
            'status' => 'created',
            'createtime' => $now,
            'updatetime' => $now,
        ]);
    }

    /**
     * @return array{ok: bool, msg: string, order?: Order}
     */
    public function payOrder(Order $order, ?string $driver = null): array
    {
        $driverName = $driver ?? (string) config('cms.payment.driver', 'balance');

        return DB::transaction(function () use ($order, $driverName) {
            $locked = Order::query()->whereKey($order->getKey())->lockForUpdate()->first();
            if (! $locked) {
                return ['ok' => false, 'msg' => '订单不存在'];
            }

            $result = $this->driver($driverName)->pay($locked);

            return array_merge($result, ['order' => $locked->fresh()]);
        });
    }

    public function recharge(Member $member, float $amount, string $memo = '余额充值'): void
    {
        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw new \InvalidArgumentException('充值金额无效');
        }

        DB::transaction(function () use ($member, $amount, $memo) {
            $locked = Member::query()->whereKey($member->id)->lockForUpdate()->first();
            if (! $locked) {
                return;
            }
            $before = round((float) $locked->money, 2);
            $after = round($before + $amount, 2);
            $locked->money = $after;
            $locked->save();

            UserMoneyLog::query()->create([
                'member_id' => $locked->id,
                'money' => $amount,
                'before' => $before,
                'after' => $after,
                'memo' => $memo,
                'createtime' => time(),
            ]);
        });
    }

    public function findProduct(string $type, int $id): ?Model
    {
        return match ($type) {
            'page' => CmsPage::query()->whereKey($id)->where('status', 'normal')->whereNull('deletetime')->first(),
            default => Archive::query()->whereKey($id)->where('status', 'normal')->whereNull('deletetime')->first(),
        };
    }
}
