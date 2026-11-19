<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Cache;

class Dictionary
{
    public function __construct(protected AdminCache $adminCache) {}

    /**
     * @return array<string, string>
     */
    public function get(string $name, int $ttl = 0): array
    {
        $ttl = $ttl > 0 ? $ttl : $this->adminCache->ttl();

        return Cache::remember("cms.dict.{$name}", $ttl, function () use ($name) {
            $row = $this->adminCache->configRow($name);
            if (! $row) {
                return [];
            }

            if (($row['type'] ?? '') === 'array' && ! empty($row['content'])) {
                return $this->parseLines((string) $row['content']);
            }

            if (! empty($row['value'])) {
                $decoded = json_decode((string) $row['value'], true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }

            return [];
        });
    }

    public function label(string $dict, string $key): string
    {
        $map = $this->get($dict);

        return $map[$key] ?? $key;
    }

    /**
     * @return array<string, string>
     */
    protected function parseLines(string $content): array
    {
        $map = [];
        foreach (preg_split('/\r\n|\r|\n/', $content) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (str_contains($line, '|')) {
                [$k, $v] = array_pad(explode('|', $line, 2), 2, '');
                $map[trim($k)] = trim($v);
            } elseif (str_contains($line, ':')) {
                [$k, $v] = array_pad(explode(':', $line, 2), 2, '');
                $map[trim($k)] = trim($v);
            }
        }

        return $map;
    }

    public function flush(string $name): void
    {
        Cache::forget("cms.dict.{$name}");
    }
}
