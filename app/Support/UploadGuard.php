<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class UploadGuard
{
    /**
     * @return array<int, string>
     */
    public static function allowedExtensions(): array
    {
        return array_map('strtolower', (array) config('cms.upload.allowed_extensions', [
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar', 'mp4', 'mp3',
        ]));
    }

    /**
     * @return array<int, string>
     */
    public static function blockedExtensions(): array
    {
        return array_map('strtolower', (array) config('cms.upload.blocked_extensions', [
            'php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'php8', 'exe', 'bat', 'cmd', 'sh', 'js', 'html', 'htm', 'htaccess',
        ]));
    }

    public static function assertUploadedFile(UploadedFile $file): void
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if (in_array($ext, self::blockedExtensions(), true)) {
            throw ValidationException::withMessages(['file' => '不允许上传该类型文件']);
        }
        if ($ext !== '' && ! in_array($ext, self::allowedExtensions(), true)) {
            throw ValidationException::withMessages(['file' => '文件类型不在允许列表中']);
        }
        $mimes = (array) config('cms.upload.allowed_mimes', [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
            'application/pdf', 'application/zip', 'video/mp4', 'audio/mpeg',
        ]);
        $mime = (string) $file->getMimeType();
        if ($mimes !== [] && $mime !== '' && ! in_array($mime, $mimes, true)) {
            throw ValidationException::withMessages(['file' => 'MIME 类型不允许']);
        }
    }

    public static function assertFilename(string $filename): void
    {
        $filename = basename($filename);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($ext === '' || in_array($ext, self::blockedExtensions(), true)) {
            throw ValidationException::withMessages(['filename' => '不允许的文件名或扩展名']);
        }
        if (! in_array($ext, self::allowedExtensions(), true)) {
            throw ValidationException::withMessages(['filename' => '文件类型不在允许列表中']);
        }
    }
}
