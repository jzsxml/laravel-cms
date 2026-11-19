<?php

namespace App\Services\Cms;

use App\Models\Admin\Archive;
use App\Models\Admin\Comment;
use App\Support\SiteConfig;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CommentNotifier
{
    public function notify(Comment $comment, Archive $archive): void
    {
        $to = SiteConfig::get('comment_notify_email', '');
        if ($to === '') {
            Log::info('CMS 评论通知（未配置邮箱）', [
                'archive' => $archive->title,
                'content' => $comment->content,
            ]);

            return;
        }

        try {
            Mail::raw(
                "文章：{$archive->title}\n昵称：{$comment->member_nickname}\n内容：\n{$comment->content}",
                fn ($message) => $message->to($to)->subject('['.SiteConfig::get('sitename', 'CMS').'] 新评论')
            );
        } catch (\Throwable $e) {
            Log::warning('评论邮件发送失败', ['error' => $e->getMessage()]);
        }
    }
}
