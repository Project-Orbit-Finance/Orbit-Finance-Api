<?php

declare(strict_types=1);

namespace Orbit\Finance\Support;

final class TokenManager
{
    private string $secret;

    public function __construct()
    {
        $this->secret = getenv('ORBIT_APP_KEY') ?: 'orbit-local-development-secret';
    }

    public function issueAccessToken(string $email): string
    {
        return $this->encode([
            'type' => 'access',
            'sub' => $email,
            'iat' => time(),
            'exp' => time() + 900,
        ]);
    }

    public function issueRefreshToken(string $email): string
    {
        return $this->encode([
            'type' => 'refresh',
            'sub' => $email,
            'iat' => time(),
            'exp' => time() + 60 * 60 * 24 * 30,
        ]);
    }

    public function parse(string $token): ?array
    {
        $decoded = base64_decode($token, true);
        if ($decoded === false) {
            return null;
        }

        $payload = json_decode($decoded, true);
        if (!is_array($payload) || !isset($payload['payload'], $payload['signature'])) {
            return null;
        }

        $expectedSignature = hash_hmac('sha256', json_encode($payload['payload'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $this->secret);
        if (!hash_equals($expectedSignature, (string) $payload['signature'])) {
            return null;
        }

        if (!is_array($payload['payload']) || !isset($payload['payload']['exp']) || !is_int($payload['payload']['exp'])) {
            return null;
        }

        if ($payload['payload']['exp'] < time()) {
            return null;
        }

        return $payload['payload'];
    }

    private function encode(array $payload): string
    {
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $signature = hash_hmac('sha256', $body, $this->secret);

        return base64_encode(json_encode([
            'payload' => $payload,
            'signature' => $signature,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
