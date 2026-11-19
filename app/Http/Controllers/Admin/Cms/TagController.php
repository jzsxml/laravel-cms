<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Admin\CrudController;
use App\Models\Admin\Tag;
use App\Support\ArchiveSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TagController extends CrudController
{
    protected string $modelClass = Tag::class;

    protected string $title = '标签管理';

    protected array $listFields = ['id', 'name', 'slug', 'weigh', 'createtime'];

    protected array $searchFields = ['name', 'slug'];

    protected array $formFields = [
        'name' => 'text:名称',
        'slug' => 'text:Slug',
        'weigh' => 'number:权重',
    ];

    protected function formExtras(): array
    {
        return [
            'slugHint' => '留空按名称生成；前台 /tag/{slug}',
        ];
    }

    protected function validateSave(Request $request, ?Model $model = null): array
    {
        $id = $model?->getKey();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'slug' => [
                'nullable', 'string', 'max:80',
                Rule::unique(Tag::class, 'slug')->ignore($id),
            ],
            'weigh' => ['nullable', 'integer', 'min:0'],
        ]);

        $slug = trim((string) ($data['slug'] ?? ''));
        if ($slug === '') {
            $slug = ArchiveSlug::slugify($data['name']);
            if ($slug === '') {
                $slug = 'tag-'.bin2hex(random_bytes(3));
            }
        } else {
            $slug = ArchiveSlug::slugify($slug);
        }
        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            throw ValidationException::withMessages(['slug' => 'Slug 格式无效']);
        }

        $base = $slug;
        $n = 1;
        while (Tag::query()->when($id, fn ($q) => $q->where('id', '!=', $id))->where('slug', $slug)->exists()) {
            $n++;
            $slug = $base.'-'.$n;
        }
        $data['slug'] = $slug;
        $data['weigh'] = (int) ($data['weigh'] ?? 0);

        return $data;
    }
}
