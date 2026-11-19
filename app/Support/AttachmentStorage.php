<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentStorage
{
    public static function deleteByUrl(string $url): void
    {
        if ($url === '') {
            return;
        }

        $disks = array_values(array_unique(array_merge(
            [UploadDisk::diskName(), 'public'],
            (array) config('cms.upload.cloud_disks', [])
        )));

        foreach ($disks as $disk) {
            if (! config("filesystems.disks.{$disk}")) {
                continue;
            }
            $base = UploadDisk::publicBaseUrl($disk);
            if ($base === '' || ! Str::startsWith($url, $base)) {
                continue;
            }
            $relative = ltrim(Str::after($url, $base), '/');
            if ($disk === 'public' && Str::startsWith($relative, 'storage/')) {
                $relative = ltrim(Str::after($relative, 'storage/'), '/');
            }
            if ($relative !== '') {
                Storage::disk($disk)->delete($relative);

                return;
            }
        }

        if (Str::startsWith($url, '/storage/')) {
            Storage::disk('public')->delete(ltrim(Str::after($url, '/storage/'), '/'));
        }
    }
}
