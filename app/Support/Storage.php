<?php

declare(strict_types=1);

namespace Orbit\Finance\Support;

final class Storage
{
    public static function path(string $file): string
    {
        return dirname(__DIR__, 2) . '/storage/app/' . ltrim($file, '/');
    }

    public static function read(string $file, array $default = []): array
    {
        $path = self::path($file);

        if (!is_file($path)) {
            return $default;
        }

        $contents = file_get_contents($path);
        if ($contents === false || $contents === '') {
            return $default;
        }

        $decoded = json_decode($contents, true);
        return is_array($decoded) ? $decoded : $default;
    }

    public static function write(string $file, array $data): void
    {
        $path = self::path($file);
        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
