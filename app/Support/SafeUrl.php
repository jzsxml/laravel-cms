<?php

namespace App\Support;

class SafeUrl
{
    /**
     * 用于 img/src、a/href：仅允许相对路径或 http(s)，拒绝 javascript: 等。
     */
    public static function forDisplay(?string $url): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }
        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return $url;
        }
        if (preg_match('#^https?://#i', $url) === 1) {
            return $url;
        }

        return '';
    }
}
