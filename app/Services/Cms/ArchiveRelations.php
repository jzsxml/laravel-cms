<?php

namespace App\Services\Cms;

use App\Models\Admin\Archive;
use App\Models\Admin\Category;
use App\Models\Admin\Tag;
use App\Support\ArchiveSlug;

class ArchiveRelations
{
    public function syncTags(Archive $archive, string $tagNames): void
    {
        $names = $this->splitList($tagNames);
        $ids = [];
        foreach ($names as $name) {
            if ($name === '') {
                continue;
            }
            $slug = $this->uniqueTagSlug($name);
            $tag = Tag::query()->firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'weigh' => 0]
            );
            if ($tag->name !== $name) {
                $tag->update(['name' => $name]);
            }
            $ids[] = $tag->id;
        }
        $archive->tags()->sync($ids);
    }

    public function syncExtraCategoryIds(Archive $archive, string $raw, int $primaryId): void
    {
        $ids = array_filter(array_map('intval', $this->splitList($raw)));
        $ids = array_values(array_unique(array_filter($ids, fn ($id) => $id > 0 && $id !== $primaryId)));

        $valid = Category::query()
            ->whereIn('id', $ids)
            ->where('type', 'article')
            ->pluck('id')
            ->all();

        $archive->category_ids = implode(',', $valid);
        $archive->save();
    }

    /**
     * @return array<int, string>
     */
    public function splitList(string $raw): array
    {
        $parts = preg_split('/[,，\s]+/u', trim($raw)) ?: [];

        return array_values(array_filter(array_map('trim', $parts)));
    }

    public function tagNamesForForm(Archive $archive): string
    {
        return $archive->tags()->pluck('name')->implode(',');
    }

    protected function uniqueTagSlug(string $name): string
    {
        $slug = ArchiveSlug::slugify($name);
        if ($slug === '') {
            $slug = 'tag-'.bin2hex(random_bytes(3));
        }
        $base = $slug;
        $n = 1;
        while (Tag::query()->where('slug', $slug)->exists()) {
            $n++;
            $slug = $base.'-'.$n;
        }

        return $slug;
    }
}
