<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Admin\Category;
use App\Models\Admin\CmsPage;
use App\Models\Api\Member;
use App\Support\PortalArchiveQuery;
use App\Support\SiteConfig;
use Illuminate\View\View;

class MemberHomeController extends Controller
{
    public function show(string $username): View
    {
        $member = Member::query()->where('username', $username)->where('status', 'normal')->firstOrFail();

        $archives = PortalArchiveQuery::published()
            ->where('member_id', $member->id)
            ->with('category')
            ->orderByDesc('publishtime')
            ->limit(30)
            ->get();

        $nav = Category::query()
            ->where('type', 'article')
            ->where('status', 'normal')
            ->where('pid', 0)
            ->orderByDesc('weigh')
            ->get();

        return view('portal.member', [
            'member' => $member,
            'archives' => $archives,
            'nav' => $nav,
            'site' => SiteConfig::siteGroup(),
            'navPages' => CmsPage::query()
                ->where('status', 'normal')
                ->whereNull('deletetime')
                ->where('flag', 'like', '%nav%')
                ->orderByDesc('weigh')
                ->get(),
        ]);
    }
}
