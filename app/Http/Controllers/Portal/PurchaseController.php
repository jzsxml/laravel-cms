<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Admin\Archive;
use App\Models\Admin\Category;
use App\Models\Admin\CmsPage;
use App\Services\Cms\ContentAccess;
use App\Services\Cms\OrderService;
use App\Services\Member\MemberAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(
        protected MemberAuth $memberAuth,
        protected OrderService $orders,
        protected ContentAccess $contentAccess,
    ) {}

    public function archive(Request $request, string $nickname, string $slug): RedirectResponse
    {
        $member = $this->memberAuth->member();
        if (! $member) {
            return redirect()->route('member.login')->with('error', '请先登录会员');
        }

        $category = Category::query()
            ->where('nickname', $nickname)
            ->where('type', 'article')
            ->where('status', 'normal')
            ->firstOrFail();

        $archive = Archive::query()
            ->where('slug', $slug)
            ->where('status', 'normal')
            ->whereNull('deletetime')
            ->firstOrFail();

        if ((int) $archive->category_id !== (int) $category->id) {
            abort(404);
        }

        if (! $this->contentAccess->isPaidContent($archive)) {
            return back()->with('error', '该文章无需购买');
        }

        if ($this->contentAccess->canViewFull($archive, $member)) {
            return back()->with('success', '您已解锁该内容');
        }

        $order = $this->orders->createContentOrder($member, $archive);
        $driver = (string) $request->input('pay_method', config('cms.payment.driver', 'balance'));
        $result = $this->orders->payOrder($order, $driver);

        return back()->with($result['ok'] ? 'success' : 'error', $result['msg']);
    }

    public function page(Request $request, string $slug): RedirectResponse
    {
        $member = $this->memberAuth->member();
        if (! $member) {
            return redirect()->route('member.login')->with('error', '请先登录会员');
        }

        $page = CmsPage::query()
            ->where('slug', $slug)
            ->where('status', 'normal')
            ->whereNull('deletetime')
            ->firstOrFail();

        if (! $this->contentAccess->isPaidContent($page)) {
            return back()->with('error', '该单页无需购买');
        }

        if ($this->contentAccess->canViewFull($page, $member)) {
            return back()->with('success', '您已解锁该内容');
        }

        $order = $this->orders->createContentOrder($member, $page);
        $driver = (string) $request->input('pay_method', config('cms.payment.driver', 'balance'));
        $result = $this->orders->payOrder($order, $driver);

        return back()->with($result['ok'] ? 'success' : 'error', $result['msg']);
    }
}
