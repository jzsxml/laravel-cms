<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Admin\CrudController;
use App\Models\Admin\CmsPage;
use App\Support\ArchiveSlug;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PageController extends CrudController
{
    protected string $modelClass = CmsPage::class;

    protected string $title = '单页管理';

    protected array $listFields = ['id', 'title', 'slug', 'template', 'status', 'weigh', 'updatetime'];

    protected array $searchFields = ['title', 'slug'];

    protected array $formFields = [
        'title' => 'text:标题',
        'slug' => 'text:URL Slug',
        'template' => 'select:模板',
        'image' => 'image:封面',
        'description' => 'textarea:摘要',
        'keywords' => 'text:SEO关键字',
        'flag' => 'text:标志',
        'pay_type' => 'select:付费类型',
        'price' => 'number:价格(元)',
        'pay_ratio' => 'number:试读比例%',
        'download_url' => 'text:附件URL',
        'weigh' => 'number:权重',
        'status' => 'select:状态',
        'content' => 'editor:正文',
    ];

    protected bool $softDelete = true;

    protected string $softDeleteColumn = 'deletetime';

    protected ?string $headingFilterField = 'status';

    /** @var array<string, string> */
    protected array $headingFilterOptions = [
        '' => '全部',
        'draft' => '草稿',
        'normal' => '已发布',
        'hidden' => '已下线',
    ];

    protected function formExtras(): array
    {
        return [
            'statusOptions' => [
                'draft' => '草稿',
                'normal' => '已发布',
                'hidden' => '已下线',
            ],
            'templateOptions' => [
                'default' => '默认',
                'contact' => '联系我们',
            ],
            'slugHint' => '前台地址 /p/{slug}；留空按标题生成拼音。',
            'flagHint' => '含 nav 时显示在门户顶栏（如：nav）',
            'payTypeOptions' => ['free' => '免费', 'pay' => '付费'],
            'payHint' => '付费单页支持试读与附件下载，支付走会员余额',
        ];
    }

    protected function validateSave(Request $request, ?Model $model = null): array
    {
        $id = $model?->getKey();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => [
                'nullable', 'string', 'max:120',
                Rule::unique(CmsPage::class, 'slug')->ignore($id),
            ],
            'template' => ['required', 'in:default,contact'],
            'image' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'keywords' => ['nullable', 'string', 'max:200'],
            'flag' => ['nullable', 'string', 'max:100'],
            'weigh' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,normal,hidden'],
            'content' => ['nullable', 'string'],
            'pay_type' => ['required', 'in:free,pay'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'pay_ratio' => ['nullable', 'integer', 'min:0', 'max:100'],
            'download_url' => ['nullable', 'string', 'max:255'],
        ]);

        $slug = trim((string) ($data['slug'] ?? ''));
        if ($slug === '') {
            $slug = ArchiveSlug::slugify($data['title']);
            if ($slug === '') {
                $slug = 'page-'.bin2hex(random_bytes(3));
            }
            $slug = $this->ensurePageSlugUnique($slug, $id ? (int) $id : null);
        } else {
            $slug = ArchiveSlug::slugify($slug);
            if (! ArchiveSlug::validate($slug)) {
                throw ValidationException::withMessages(['slug' => 'Slug 格式无效或为保留字']);
            }
            $slug = $this->ensurePageSlugUnique($slug, $id ? (int) $id : null);
        }
        $data['slug'] = $slug;
        $data['weigh'] = (int) ($data['weigh'] ?? 0);
        $data['pay_type'] = $data['pay_type'] ?? 'free';
        $data['price'] = $data['pay_type'] === 'pay' ? round((float) ($data['price'] ?? 0), 2) : 0;
        $data['pay_ratio'] = (int) ($data['pay_ratio'] ?? 0);
        $data['download_url'] = trim((string) ($data['download_url'] ?? ''));

        if (array_key_exists('content', $data)) {
            $data['content'] = HtmlSanitizer::clean($data['content']);
        }

        return $data;
    }

    protected function ensurePageSlugUnique(string $slug, ?int $ignoreId): string
    {
        $base = $slug;
        $n = 1;
        while (CmsPage::query()->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->where('slug', $slug)->exists()) {
            $n++;
            $slug = $base.'-'.$n;
        }

        return $slug;
    }

    protected function transformRows(array $rows): array
    {
        return array_map(function ($row) {
            $arr = $row instanceof Model ? $row->toArray() : (array) $row;
            $arr['status'] = match ($arr['status'] ?? '') {
                'draft' => '草稿',
                'hidden' => '下线',
                default => '已发布',
            };
            if (! empty($arr['updatetime'])) {
                $arr['updatetime'] = date('Y-m-d H:i', (int) $arr['updatetime']);
            }

            return $arr;
        }, $rows);
    }
}
