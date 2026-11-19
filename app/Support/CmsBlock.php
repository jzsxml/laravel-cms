<?php

namespace App\Support;

use App\Models\Admin\CmsBlock as BlockModel;
use Illuminate\Support\Facades\Cache;

class CmsBlock
{
    public static function get(string $name): ?BlockModel
    {
        $key = 'cms_block_'.$name;
        $ttl = max(60, (int) config('cms.cache.ttl', 3600));

        $cached = Cache::get($key);
        if ($cached instanceof BlockModel) {
            return $cached;
        }
        if (is_array($cached)) {
            return (new BlockModel)->newFromBuilder($cached);
        }
        if ($cached !== null) {
            Cache::forget($key);
        }

        /** @var array<string, mixed>|null $attrs */
        $attrs = Cache::remember($key, $ttl, function () use ($name) {
            $block = BlockModel::query()
                ->where('name', $name)
                ->where('status', 'normal')
                ->first();

            return $block?->getAttributes();
        });

        return is_array($attrs) ? (new BlockModel)->newFromBuilder($attrs) : null;
    }

    public static function html(string $name, string $default = ''): string
    {
        $block = self::get($name);
        if (! $block) {
            return $default;
        }

        if ($block->type === 'image') {
            $url = trim($block->content ?? '');

            return $url !== '' ? '<img src="'.e($url).'" alt="'.e($block->title).'" class="img-fluid">' : $default;
        }

        return HtmlSanitizer::clean($block->content ?? $default);
    }

    public static function forget(string $name): void
    {
        Cache::forget('cms_block_'.$name);
    }
}
