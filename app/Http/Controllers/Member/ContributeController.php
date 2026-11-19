<?php

namespace App\Http\Controllers\Member;

use App\Models\Admin\Archive;
use App\Models\Admin\Category;
use App\Support\ArchiveSlug;
use App\Support\HtmlSanitizer;
use App\Support\SiteConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContributeController extends Controller
{
    public function index(): View
    {
        $member = $this->memberAuth->member();
        $list = Archive::query()
            ->where('member_id', $member?->id ?? 0)
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('member.contribute.index', [
            'member' => $member,
            'archives' => $list,
            'categories' => $this->allowedCategories(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $member = $this->memberAuth->member();
        if (! $member) {
            return redirect()->route('member.login');
        }

        $allowed = $this->allowedCategoryIds();
        if ($allowed === []) {
            return back()->with('error', '管理员未开放可投稿栏目');
        }
        $data = $request->validate([
            'category_id' => ['required', 'integer', 'in:'.implode(',', $allowed)],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
        ]);

        Archive::query()->create([
            'category_id' => (int) $data['category_id'],
            'member_id' => $member->id,
            'admin_id' => 0,
            'slug' => ArchiveSlug::fromTitle($data['title']),
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'content' => HtmlSanitizer::clean($data['content']),
            'status' => 'pending',
        ]);

        return redirect()->route('member.contribute')->with('success', '投稿已提交，等待审核');
    }

    /**
     * @return array<int, int>
     */
    protected function allowedCategoryIds(): array
    {
        $raw = SiteConfig::get('contribute_category_ids', 'news,products,cases');
        $nicknames = array_filter(array_map('trim', preg_split('/[,，\s]+/', $raw) ?: []));

        if ($nicknames === []) {
            return Category::query()->where('type', 'article')->where('status', 'normal')->pluck('id')->all();
        }

        return Category::query()
            ->whereIn('nickname', $nicknames)
            ->where('type', 'article')
            ->where('status', 'normal')
            ->pluck('id')
            ->all();
    }

    protected function allowedCategories()
    {
        $ids = $this->allowedCategoryIds();

        return Category::query()->whereIn('id', $ids)->orderByDesc('weigh')->get();
    }
}
