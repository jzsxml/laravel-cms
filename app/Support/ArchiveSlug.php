<?php

namespace App\Support;

use App\Models\Admin\Archive;
use Illuminate\Support\Str;
use Overtrue\Pinyin\Pinyin;

class ArchiveSlug
{
    /** @var array<int, string> */
    protected static array $reserved = [
        'admin', 'user', 'api', 'c', 'p', 's', 'u', 'm', 'search', 'tag', 'storage', 'up', 'login', 'register', 'member',
    ];

    public static function fromTitle(string $title, ?int $ignoreId = null): string
    {
        $title = trim(strip_tags($title));
        $slug = self::slugify($title);
        if ($slug === '') {
            $slug = 'post-'.bin2hex(random_bytes(4));
        }

        return self::ensureUnique($slug, $ignoreId);
    }

    public static function slugify(string $text): string
    {
        $text = trim(strip_tags($text));
        if ($text === '') {
            return '';
        }

        if (preg_match('/[\x{4e00}-\x{9fff}]/u', $text)) {
            $slug = Pinyin::permalink($text, '-');
        } else {
            $slug = Str::slug($text, '-');
        }

        $slug = strtolower(preg_replace('/[^a-z0-9\-]+/', '-', $slug) ?? '');
        $slug = trim(preg_replace('/-+/', '-', $slug) ?? '', '-');

        if (strlen($slug) > 100) {
            $slug = rtrim(substr($slug, 0, 100), '-');
        }

        return $slug;
    }

    public static function validate(string $slug): bool
    {
        if (strlen($slug) < 3 || strlen($slug) > 120) {
            return false;
        }
        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            return false;
        }
        if (in_array($slug, self::reserved(), true)) {
            return false;
        }

        return true;
    }

    public static function ensureUnique(string $slug, ?int $ignoreId = null): string
    {
        $base = $slug;
        $n = 1;
        while (self::exists($slug, $ignoreId)) {
            $n++;
            $slug = $base.'-'.$n;
            if (strlen($slug) > 120) {
                $slug = rtrim(substr($base, 0, 110), '-').'-'.$n;
            }
        }

        return $slug;
    }

    protected static function exists(string $slug, ?int $ignoreId): bool
    {
        $query = Archive::query()->where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    /** @return array<int, string> */
    public static function reserved(): array
    {
        $extra = (array) config('cms.portal.reserved_slugs', []);

        return array_values(array_unique(array_merge(self::$reserved, $extra)));
    }
}
