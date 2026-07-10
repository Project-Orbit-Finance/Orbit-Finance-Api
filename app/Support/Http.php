<?php

declare(strict_types=1);

namespace Orbit\Finance\Support;

final class Http
{
    public static function json(array $payload, int $status = 200, array $headers = []): void
    {
        http_response_code($status);
        header('Content-Type: application/json');

        foreach ($headers as $key => $value) {
            header($key . ': ' . $value);
        }

        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
