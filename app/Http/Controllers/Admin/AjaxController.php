<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\Attachment;
use App\Support\UploadDisk;
use App\Support\UploadGuard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AjaxController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('file');
        UploadGuard::assertUploadedFile($file);
        $stored = UploadDisk::putUploadedFile($file);
        $sha1 = sha1_file($file->getRealPath());

        $attachment = Attachment::query()->create([
            'category' => (string) $request->input('category', ''),
            'admin_id' => $this->adminAuth->adminId() ?? 0,
            'url' => $stored['url'],
            'filename' => $file->getClientOriginalName(),
            'filesize' => $file->getSize() ?: 0,
            'mimetype' => $file->getMimeType() ?? '',
            'uploadtime' => time(),
            'storage' => $stored['storage'],
            'sha1' => $sha1,
        ]);

        $this->adminAuth->recordLog($request, '上传', '附件上传: '.$attachment->filename);

        return response()->json([
            'code' => 1,
            'msg' => '上传成功',
            'data' => [
                'id' => $attachment->id,
                'url' => $stored['url'],
                'fullurl' => $stored['url'],
            ],
        ]);
    }

    public function selectpage(Request $request): JsonResponse
    {
        $source = (string) $request->input('source', $request->input('custom.source', 'category'));
        $map = config('cms.crud.selectpage_sources', []);
        /** @var class-string<Model>|null $modelClass */
        $modelClass = $map[$source] ?? null;
        if (! $modelClass || ! class_exists($modelClass)) {
            return response()->json(['total' => 0, 'list' => []]);
        }

        $keyField = self::sanitizeSelectField((string) $request->input('keyField', 'id'), 'id');
        $showField = self::sanitizeSelectField((string) $request->input('showField', 'name'), 'name');
        if ($source === 'admin') {
            $showField = 'username';
        }
        $q = trim((string) $request->input('q', $request->input('search', '')));

        $query = $modelClass::query();
        if ($request->filled('id')) {
            $query->whereKey((int) $request->input('id'));
        } elseif ($q !== '') {
            $query->where(function ($builder) use ($showField, $q) {
                $builder->where($showField, 'like', '%'.$q.'%');
                if ($showField !== 'nickname') {
                    $builder->orWhere('nickname', 'like', '%'.$q.'%');
                }
            });
        }

        if ($source === 'category') {
            $type = (string) $request->input('type', $request->input('custom.type', ''));
            if ($type !== '') {
                $query->where('type', $type);
            }
        }

        $page = max(1, (int) $request->input('pageNumber', $request->input('page', 1)));
        $pageSize = max(1, min(50, (int) $request->input('pageSize', $request->input('limit', 10))));
        $total = (clone $query)->count();
        $rows = $query->orderByDesc('id')->forPage($page, $pageSize)->get();

        $list = $rows->map(function (Model $row) use ($keyField, $showField, $source) {
            $display = $row->getAttribute($showField);
            if ($source === 'admin') {
                $nick = $row->getAttribute('nickname');
                $display = $nick ? "{$nick} ({$row->getAttribute('username')})" : $row->getAttribute('username');
            } elseif ($display === null || $display === '') {
                $display = $row->getAttribute('name') ?? $row->getAttribute('title');
            }

            return [
                $keyField => $row->getAttribute($keyField),
                $showField => $row->getAttribute($showField),
                'id' => $row->getAttribute('id'),
                'name' => (string) $display,
            ];
        })->values();

        return response()->json(['total' => $total, 'list' => $list->all()]);
    }

    public function uploadChunk(Request $request): JsonResponse
    {
        $request->validate([
            'chunkid' => ['required', 'string', 'max:64'],
            'chunkindex' => ['required', 'integer', 'min:0'],
            'chunkcount' => ['required', 'integer', 'min:1'],
            'file' => ['required', 'file'],
        ]);

        $chunkId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $request->input('chunkid'));
        $disk = Storage::disk(config('cms.upload.chunk_disk', 'local'));
        $base = trim(config('cms.upload.chunk_path', 'chunks'), '/').'/'.$chunkId;
        $disk->putFileAs($base, $request->file('file'), (string) $request->input('chunkindex'));

        return response()->json(['code' => 1, 'msg' => '分片已接收']);
    }

    public function uploadMerge(Request $request): JsonResponse
    {
        $request->validate([
            'chunkid' => ['required', 'string', 'max:64'],
            'chunkcount' => ['required', 'integer', 'min:1'],
            'filename' => ['required', 'string', 'max:255'],
        ]);

        $chunkId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $request->input('chunkid'));
        $chunkCount = (int) $request->input('chunkcount');
        $filename = basename((string) $request->input('filename'));
        UploadGuard::assertFilename($filename);

        $chunkDisk = Storage::disk(config('cms.upload.chunk_disk', 'local'));
        $base = trim(config('cms.upload.chunk_path', 'chunks'), '/').'/'.$chunkId;
        $mergedRelative = 'uploads/'.date('Ymd').'/'.Str::uuid().'_'.$filename;
        $uploadDiskName = UploadDisk::diskName();
        $publicDisk = Storage::disk($uploadDiskName);
        $publicDisk->makeDirectory(dirname($mergedRelative));

        $absolute = $publicDisk->path($mergedRelative);
        $out = fopen($absolute, 'wb');
        if ($out === false) {
            return response()->json(['code' => 0, 'msg' => '无法创建目标文件']);
        }

        for ($i = 0; $i < $chunkCount; $i++) {
            $part = $base.'/'.$i;
            if (! $chunkDisk->exists($part)) {
                fclose($out);
                @unlink($absolute);

                return response()->json(['code' => 0, 'msg' => "缺少分片 {$i}"]);
            }
            $stream = $chunkDisk->readStream($part);
            stream_copy_to_stream($stream, $out);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
        fclose($out);

        $chunkDisk->deleteDirectory($base);

        $url = UploadDisk::publicUrl($mergedRelative, $uploadDiskName);
        $sha1 = sha1_file($absolute) ?: '';

        $attachment = Attachment::query()->create([
            'admin_id' => $this->adminAuth->adminId() ?? 0,
            'url' => $url,
            'filename' => $filename,
            'filesize' => filesize($absolute) ?: 0,
            'mimetype' => mime_content_type($absolute) ?: '',
            'uploadtime' => time(),
            'storage' => $uploadDiskName === 'public' ? 'local' : $uploadDiskName,
            'sha1' => $sha1,
        ]);

        return response()->json([
            'code' => 1,
            'msg' => '合并成功',
            'data' => ['id' => $attachment->id, 'url' => $url, 'fullurl' => $url],
        ]);
    }

    protected static function sanitizeSelectField(string $field, string $default): string
    {
        $field = strtolower(trim($field));
        if ($field === '' || ! preg_match('/^[a-z][a-z0-9_]*$/', $field)) {
            return $default;
        }

        return $field;
    }
}
