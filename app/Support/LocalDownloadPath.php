<?php

namespace App\Support;

class LocalDownloadPath
{
    public static function resolve(string $url): ?string
    {
        $url = trim($url);
        if ($url === '' || str_contains($url, '..') || str_contains($url, "\0")) {
            return null;
        }
        if (preg_match('#^https?://#i', $url) === 1) {
            return null;
        }

        $relative = ltrim(parse_url($url, PHP_URL_PATH) ?? $url, '/');
        if ($relative === '') {
            return null;
        }

        $candidates = [
            public_path($relative),
            storage_path('app/public/'.$relative),
        ];

        foreach ($candidates as $path) {
            $real = realpath($path);
            if ($real === false || ! is_file($real)) {
                continue;
            }
            foreach ([realpath(public_path()), realpath(storage_path('app/public'))] as $root) {
                if ($root !== false && str_starts_with($real, $root.DIRECTORY_SEPARATOR)) {
                    return $real;
                }
            }
        }

        return null;
    }
}
