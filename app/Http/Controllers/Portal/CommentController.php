<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Admin\Archive;
use App\Models\Admin\Category;
use App\Models\Admin\Comment;
use App\Services\Cms\CommentNotifier;
use App\Services\Member\MemberAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, string $nickname, string $slug, MemberAuth $memberAuth): RedirectResponse
    {
        $member = $memberAuth->member();
        if (! $member) {
            return redirect()->route('member.login')->with('error', '请登录后评论');
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

        $data = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
        ]);

        $comment = Comment::query()->create([
            'archive_id' => $archive->id,
            'member_id' => $member->id,
            'member_nickname' => $member->nickname ?: $member->username,
            'content' => $data['content'],
            'ip' => $request->ip() ?? '',
            'status' => 'normal',
            'createtime' => time(),
        ]);

        app(CommentNotifier::class)->notify($comment, $archive);

        return back()->with('success', '评论已提交');
    }
}
