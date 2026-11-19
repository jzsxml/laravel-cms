<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Admin\Archive;
use App\Models\Admin\Category;
use App\Services\Member\MemberAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LikeController extends Controller
{
    public function __construct(
        protected MemberAuth $memberAuth,
    ) {}

    public function archive(Request $request, string $nickname, string $slug): RedirectResponse
    {
        if (! $this->memberAuth->check()) {
            return redirect()->route('member.login')->with('error', '请先登录后点赞');
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

        $key = 'liked_archive_'.$archive->id;
        if (Session::get($key)) {
            return back()->with('error', '您已点过赞');
        }

        $archive->increment('likes');
        Session::put($key, 1);

        return back()->with('success', '感谢点赞');
    }
}
