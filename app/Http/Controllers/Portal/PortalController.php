<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Admin\Archive;
use App\Models\Admin\Category;
use App\Models\Admin\CmsPage;
use App\Models\Admin\Comment;
use App\Models\Admin\Special;
use App\Models\Admin\Tag;
use App\Services\Cms\ContentAccess;
use App\Services\Member\MemberAuth;
use App\Support\PortalArchiveQuery;
use App\Support\PortalNav;
use App\Support\PortalTheme;
use App\Support\SiteConfig;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function __construct(
        protected ContentAccess $contentAccess,
        protected MemberAuth $memberAuth,
    ) {}

    public function home(): View
    {
        $ctx = $this->portalContext();
        $legacy = $this->legacyHomeData();
        $cms = $this->cmsHomeData();

        return view(PortalTheme::view('home'), array_merge($ctx, $legacy, $cms));
    }

    public function category(Request $request, string $nickname): View
    {
        $category = Category::query()
            ->where('nickname', $nickname)
            ->where('type', 'article')
            ->where('status', 'normal')
            ->firstOrFail();

        $perPage = 12;
        $page = max(1, (int) $request->input('page', 1));

        $query = PortalArchiveQuery::inCategory((int) $category->id)
            ->orderByDesc('weigh')
            ->orderByDesc('publishtime');

        $total = (clone $query)->count();
        $archives = $query->forPage($page, $perPage)
            ->with('category')
            ->withCount([
                'comments' => fn ($q) => $q->where('status', 'normal')->whereNull('deletetime'),
            ])
            ->get();

        return view(PortalTheme::view('category'), array_merge($this->portalContext(), [
            'category' => $category,
            'archives' => $archives,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
        ]));
    }

    public function archive(string $nickname, string $slug): View
    {
        $category = Category::query()
            ->where('nickname', $nickname)
            ->where('type', 'article')
            ->where('status', 'normal')
            ->firstOrFail();

        $archive = Archive::query()
            ->where('slug', $slug)
            ->where('status', 'normal')
            ->whereNull('deletetime')
            ->with('tags')
            ->firstOrFail();

        if ((int) $archive->category_id !== (int) $category->id) {
            abort(404);
        }

        $archive->increment('views');

        $comments = Comment::query()
            ->where('archive_id', $archive->id)
            ->where('status', 'normal')
            ->whereNull('deletetime')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $member = $this->memberAuth->member();
        $contentView = $this->contentAccess->presentContent($archive, $member);
        $canDownload = $this->contentAccess->canDownload($archive, $member);

        return view('portal.archive', array_merge($this->portalContext(), compact(
            'category', 'archive', 'comments', 'contentView', 'canDownload', 'member'
        )));
    }

    public function special(Request $request, string $slug): View
    {
        $special = Special::query()
            ->where('slug', $slug)
            ->where('status', 'normal')
            ->whereNull('deletetime')
            ->firstOrFail();

        $tagSlug = trim((string) $request->input('tag', ''));
        $query = $special->archives()
            ->where('status', 'normal')
            ->whereNull('deletetime')
            ->with('category')
            ->orderByDesc('publishtime');

        if ($tagSlug !== '') {
            $query->whereHas('tags', fn ($q) => $q->where('slug', $tagSlug));
        }

        $archives = $query->get();
        $archiveIds = $special->archives()
            ->where('status', 'normal')
            ->whereNull('deletetime')
            ->pluck('id');
        $filterTags = $archiveIds->isEmpty()
            ? collect()
            : Tag::query()
                ->whereHas('archives', fn ($q) => $q->whereIn('id', $archiveIds))
                ->orderBy('name')
                ->get();

        return view('portal.special', array_merge($this->portalContext(), [
            'special' => $special,
            'archives' => $archives,
            'filterTags' => $filterTags,
            'activeTag' => $tagSlug,
        ]));
    }

    public function page(string $slug): View
    {
        $page = CmsPage::query()
            ->where('slug', $slug)
            ->where('status', 'normal')
            ->whereNull('deletetime')
            ->firstOrFail();

        $page->increment('views');

        $template = in_array($page->template, ['default', 'contact'], true) ? $page->template : 'default';
        $member = $this->memberAuth->member();
        $contentView = $this->contentAccess->presentContent($page, $member);
        $canDownload = $this->contentAccess->canDownload($page, $member);

        return view('portal.page', array_merge($this->portalContext(), [
            'page' => $page,
            'template' => $template,
            'contentView' => $contentView,
            'canDownload' => $canDownload,
            'member' => $member,
        ]));
    }

    public function search(Request $request): View
    {
        $q = trim((string) $request->input('q', ''));
        $perPage = 15;
        $page = max(1, (int) $request->input('page', 1));

        $query = PortalArchiveQuery::search($q)->orderByDesc('publishtime');
        $total = $q === '' ? 0 : (clone $query)->count();
        $archives = $q === '' ? collect() : $query->forPage($page, $perPage)->get();

        return view('portal.search', array_merge($this->portalContext(), [
            'q' => $q,
            'archives' => $archives,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
        ]));
    }

    public function tag(Request $request, string $slug): View
    {
        $tag = Tag::query()->where('slug', $slug)->firstOrFail();
        $perPage = 12;
        $page = max(1, (int) $request->input('page', 1));

        $query = PortalArchiveQuery::published()
            ->whereHas('tags', fn ($q) => $q->whereKey($tag->id))
            ->with('category')
            ->orderByDesc('publishtime');

        $total = (clone $query)->count();
        $archives = $query->forPage($page, $perPage)->get();

        return view('portal.tag', array_merge($this->portalContext(), [
            'tag' => $tag,
            'archives' => $archives,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    public function sharedPortalContext(): array
    {
        return $this->portalContext();
    }

    /**
     * @return array<string, mixed>
     */
    protected function portalContext(): array
    {
        return [
            'nav' => $this->navCategories(),
            'navTree' => PortalNav::categoryTree(),
            'site' => SiteConfig::siteGroup(),
            'navPages' => CmsPage::query()
                ->where('status', 'normal')
                ->whereNull('deletetime')
                ->where('flag', 'like', '%nav%')
                ->orderByDesc('weigh')
                ->get(),
            'firstSpecial' => Special::query()
                ->where('status', 'normal')
                ->whereNull('deletetime')
                ->orderByDesc('weigh')
                ->first(),
        ];
    }

    protected function navCategories()
    {
        return Category::query()
            ->where('type', 'article')
            ->where('status', 'normal')
            ->where('pid', 0)
            ->orderByDesc('weigh')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    protected function legacyHomeData(): array
    {
        $recommend = Archive::query()
            ->where('status', 'normal')
            ->whereNull('deletetime')
            ->where('flag', 'like', '%recommend%')
            ->orderByDesc('weigh')
            ->orderByDesc('publishtime')
            ->limit(6)
            ->with('category')
            ->get();

        $top = Archive::query()
            ->where('status', 'normal')
            ->whereNull('deletetime')
            ->where('flag', 'like', '%top%')
            ->orderByDesc('weigh')
            ->limit(3)
            ->with('category')
            ->get();

        $latestNews = $this->latestForNickname('news', 5);
        $latestCases = $this->latestForNickname('cases', 3);

        $tagCloud = Tag::query()->orderByDesc('weigh')->orderBy('id')->limit(20)->get();

        return compact('recommend', 'top', 'latestNews', 'latestCases', 'tagCloud');
    }

    /**
     * @return array<string, mixed>
     */
    protected function cmsHomeData(): array
    {
        $base = Archive::query()
            ->where('status', 'normal')
            ->whereNull('deletetime')
            ->with('category')
            ->withCount([
                'comments' => fn ($q) => $q->where('status', 'normal')->whereNull('deletetime'),
            ]);

        $focusSlides = (clone $base)
            ->where(function ($q) {
                $q->where('flag', 'like', '%focus%')
                    ->orWhere('flag', 'like', '%recommend%');
            })
            ->where('image', '!=', '')
            ->orderByDesc('weigh')
            ->orderByDesc('publishtime')
            ->limit(5)
            ->get();

        if ($focusSlides->isEmpty()) {
            $focusSlides = (clone $base)
                ->where('image', '!=', '')
                ->orderByDesc('publishtime')
                ->limit(3)
                ->get();
        }

        $hotGallery = (clone $base)
            ->where('image', '!=', '')
            ->orderByDesc('views')
            ->orderByDesc('weigh')
            ->limit(4)
            ->get();

        $tabCategories = Category::query()
            ->where('type', 'article')
            ->where('status', 'normal')
            ->where('pid', '>', 0)
            ->orderByDesc('weigh')
            ->limit(8)
            ->get();

        if ($tabCategories->isEmpty()) {
            $tabCategories = Category::query()
                ->where('type', 'article')
                ->where('status', 'normal')
                ->where('pid', 0)
                ->orderByDesc('weigh')
                ->limit(6)
                ->get();
        }

        $recentList = (clone $base)
            ->orderByDesc('publishtime')
            ->limit(10)
            ->get();

        $sidebarLatest = (clone $base)
            ->orderByDesc('publishtime')
            ->limit(8)
            ->get();

        $sidebarRecommend = (clone $base)
            ->where('flag', 'like', '%recommend%')
            ->orderByDesc('weigh')
            ->limit(6)
            ->get();

        $downloadCatIds = Category::query()
            ->where('nickname', 'like', '%download%')
            ->orWhere('name', 'like', '%下载%')
            ->pluck('id');

        $downloadList = (clone $base)
            ->where(function ($q) use ($downloadCatIds) {
                $q->where(function ($inner) {
                    $inner->whereNotNull('download_url')->where('download_url', '!=', '');
                });
                if ($downloadCatIds->isNotEmpty()) {
                    $q->orWhereIn('category_id', $downloadCatIds);
                }
            })
            ->orderByDesc('publishtime')
            ->limit(8)
            ->get();

        return [
            'focusSlides' => $focusSlides,
            'hotGallery' => $hotGallery,
            'tabCategories' => $tabCategories,
            'recentList' => $recentList,
            'sidebarLatest' => $sidebarLatest,
            'sidebarRecommend' => $sidebarRecommend,
            'downloadList' => $downloadList,
        ];
    }

    protected function latestForNickname(string $nickname, int $limit)
    {
        $category = Category::query()
            ->where('nickname', $nickname)
            ->where('type', 'article')
            ->where('status', 'normal')
            ->first();

        if (! $category) {
            return collect();
        }

        return PortalArchiveQuery::inCategory((int) $category->id)
            ->orderByDesc('publishtime')
            ->with('category')
            ->limit($limit)
            ->get();
    }
}
