<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Admin\Archive;
use App\Models\Admin\Category;
use App\Models\Admin\CmsPage;
use App\Services\Cms\ContentAccess;
use App\Services\Member\MemberAuth;
use App\Support\LocalDownloadPath;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadController extends Controller
{
    public function __construct(
        protected MemberAuth $memberAuth,
        protected ContentAccess $contentAccess,
    ) {}

    public function archive(string $nickname, string $slug): RedirectResponse|BinaryFileResponse
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
            ->firstOrFail();

        if ((int) $archive->category_id !== (int) $category->id) {
            abort(404);
        }

        return $this->respondDownload($archive, $this->memberAuth->member());
    }

    public function page(string $slug): RedirectResponse|BinaryFileResponse
    {
        $page = CmsPage::query()
            ->where('slug', $slug)
            ->where('status', 'normal')
            ->whereNull('deletetime')
            ->firstOrFail();

        return $this->respondDownload($page, $this->memberAuth->member());
    }

    protected function respondDownload($product, $member): RedirectResponse|BinaryFileResponse
    {
        $url = trim((string) ($product->download_url ?? ''));
        if ($url === '') {
            abort(404);
        }

        if (! $this->contentAccess->canDownload($product, $member)) {
            return redirect()->back()->with('error', '请先购买后下载');
        }

        if (preg_match('#^https?://#i', $url) === 1) {
            if (! config('cms.portal.allow_external_download_redirect', false)) {
                abort(403, '外部下载链接未启用');
            }

            return redirect()->away($url);
        }

        $path = LocalDownloadPath::resolve($url);
        if ($path === null) {
            abort(404, '文件不存在');
        }

        return response()->download($path, basename($path));
    }
}
