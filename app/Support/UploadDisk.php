<?php

namespace App\Support;

use App\Services\Admin\AdminCache;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadDisk
{
    public static function diskName(): string
    {
        $disk = (string) config('cms.upload.disk', 'public');

        return $disk !== '' ? $disk : 'public';
    }

    /**
     * @return array{path: string, url: string, disk: string, storage: string}
     */
    public static function putUploadedFile(UploadedFile $file, string $baseDir = 'uploads'): array
    {
        $disk = self::diskName();
        $path = $file->store($baseDir.'/'.date('Ymd'), $disk);

        return [
            'path' => $path,
            'url' => self::publicUrl($path, $disk),
            'disk' => $disk,
            'storage' => $disk === 'public' ? 'local' : $disk,
        ];
    }

    public static function publicUrl(string $path, ?string $disk = null): string
    {
        $disk = $disk ?? self::diskName();
        $cdn = trim(app(AdminCache::class)->configValue('cdnurl'));

        if ($cdn !== '' && $disk === 'public') {
            return rtrim($cdn, '/').'/storage/'.ltrim($path, '/');
        }

        if ($cdn !== '' && in_array($disk, (array) config('cms.upload.cloud_disks', []), true)) {
            return rtrim($cdn, '/').'/'.ltrim($path, '/');
        }

        $diskUrl = (string) config("filesystems.disks.{$disk}.url", '');
        if ($diskUrl !== '' && in_array($disk, (array) config('cms.upload.cloud_disks', []), true)) {
            return rtrim($diskUrl, '/').'/'.ltrim($path, '/');
        }

        return Storage::disk($disk)->url($path);
    }

    public static function publicBaseUrl(?string $disk = null): string
    {
        $disk = $disk ?? self::diskName();
        $cdn = trim(app(AdminCache::class)->configValue('cdnurl'));

        if ($cdn !== '' && ($disk === 'public' || in_array($disk, (array) config('cms.upload.cloud_disks', []), true))) {
            if ($disk === 'public') {
                return rtrim($cdn, '/').'/storage';
            }

            return rtrim($cdn, '/');
        }

        if ($disk === 'public') {
            return rtrim(Storage::disk('public')->url(''), '/');
        }

        $configured = (string) config("filesystems.disks.{$disk}.url", '');
        if ($configured !== '') {
            return rtrim($configured, '/');
        }

        return rtrim(Storage::disk($disk)->url(''), '/');
    }
}
