<?php

namespace App\Services\Cms;

use App\Models\Admin\Archive;
use App\Models\Admin\CmsPage;
use App\Models\Admin\Order;
use App\Models\Api\Member;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Model;

class ContentAccess
{
    public function isPaidContent(Model $product): bool
    {
        return ($product->pay_type ?? 'free') === 'pay' && (float) ($product->price ?? 0) > 0;
    }

    public function memberHasVip(?Member $member): bool
    {
        if (! $member || empty($member->vip_expiretime)) {
            return false;
        }

        return (int) $member->vip_expiretime > time();
    }

    public function hasPurchased(?Member $member, string $productType, int $productId): bool
    {
        if (! $member) {
            return false;
        }

        return Order::query()
            ->where('member_id', $member->id)
            ->where('product_type', $productType)
            ->where('product_id', $productId)
            ->where('status', 'paid')
            ->exists();
    }

    public function canViewFull(Model $product, ?Member $member): bool
    {
        if (! $this->isPaidContent($product)) {
            return true;
        }
        if ($this->memberHasVip($member)) {
            return true;
        }

        $type = $product instanceof Archive ? 'archive' : ($product instanceof CmsPage ? 'page' : 'archive');

        return $this->hasPurchased($member, $type, (int) $product->getKey());
    }

    /**
     * @return array{html: string, locked: bool, price: float, product_type: string, product_id: int}
     */
    public function presentContent(Model $product, ?Member $member): array
    {
        $type = $product instanceof Archive ? 'archive' : 'page';
        $price = (float) ($product->price ?? 0);

        if ($this->canViewFull($product, $member)) {
            return [
                'html' => HtmlSanitizer::clean((string) ($product->content ?? '')),
                'locked' => false,
                'price' => $price,
                'product_type' => $type,
                'product_id' => (int) $product->getKey(),
            ];
        }

        $ratio = max(0, min(100, (int) ($product->pay_ratio ?? 0)));
        $plain = strip_tags((string) ($product->content ?? ''));
        $plain = preg_replace('/\s+/u', ' ', $plain) ?? '';
        $len = mb_strlen($plain);
        $show = $ratio > 0 ? mb_substr($plain, 0, (int) ceil($len * $ratio / 100)) : '';
        if ($show !== '' && $len > mb_strlen($show)) {
            $show .= '…';
        }
        $html = $show === '' ? '' : '<p>'.nl2br(e($show)).'</p>';

        return [
            'html' => $html,
            'locked' => true,
            'price' => $price,
            'product_type' => $type,
            'product_id' => (int) $product->getKey(),
        ];
    }

    public function canDownload(Model $product, ?Member $member): bool
    {
        $url = trim((string) ($product->download_url ?? ''));
        if ($url === '') {
            return false;
        }
        if (! $this->isPaidContent($product)) {
            return true;
        }

        return $this->canViewFull($product, $member);
    }
}
