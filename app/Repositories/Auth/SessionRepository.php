<?php

declare(strict_types=1);

namespace Orbit\Finance\Repositories\Auth;

use Orbit\Finance\Support\Storage;

final class SessionRepository
{
    public function create(string $email, string $accessToken, string $refreshToken): array
    {
        $sessions = $this->all();
        $sessions[] = [
            'email' => $email,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'created_at' => date(DATE_ATOM),
            'revoked_at' => null,
        ];
        Storage::write('sessions.json', $sessions);

        return end($sessions) ?: [];
    }

    public function findByRefreshToken(string $refreshToken): ?array
    {
        foreach ($this->all() as $session) {
            if (($session['refresh_token'] ?? null) === $refreshToken && ($session['revoked_at'] ?? null) === null) {
                return $session;
            }
        }

        return null;
    }

    public function findByAccessToken(string $accessToken): ?array
    {
        foreach ($this->all() as $session) {
            if (($session['access_token'] ?? null) === $accessToken && ($session['revoked_at'] ?? null) === null) {
                return $session;
            }
        }

        return null;
    }

    public function revokeByRefreshToken(string $refreshToken): void
    {
        $sessions = $this->all();
        foreach ($sessions as &$session) {
            if (($session['refresh_token'] ?? null) === $refreshToken && ($session['revoked_at'] ?? null) === null) {
                $session['revoked_at'] = date(DATE_ATOM);
            }
        }

        Storage::write('sessions.json', $sessions);
    }

    public function revokeByAccessToken(string $accessToken): void
    {
        $sessions = $this->all();
        foreach ($sessions as &$session) {
            if (($session['access_token'] ?? null) === $accessToken && ($session['revoked_at'] ?? null) === null) {
                $session['revoked_at'] = date(DATE_ATOM);
            }
        }

        Storage::write('sessions.json', $sessions);
    }

    public function revokeByEmail(string $email): void
    {
        $sessions = $this->all();
        foreach ($sessions as &$session) {
            if (($session['email'] ?? null) === $email && ($session['revoked_at'] ?? null) === null) {
                $session['revoked_at'] = date(DATE_ATOM);
            }
        }

        Storage::write('sessions.json', $sessions);
    }

    private function all(): array
    {
        return Storage::read('sessions.json', []);
    }
}
