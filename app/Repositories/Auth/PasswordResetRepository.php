<?php

declare(strict_types=1);

namespace Orbit\Finance\Repositories\Auth;

use Orbit\Finance\Support\Storage;

final class PasswordResetRepository
{
    public function create(string $email, string $token): array
    {
        $items = $this->all();
        $items[] = [
            'email' => $email,
            'token' => $token,
            'created_at' => date(DATE_ATOM),
            'expires_at' => date(DATE_ATOM, time() + 3600),
            'used_at' => null,
        ];
        Storage::write('password_resets.json', $items);

        return end($items) ?: [];
    }

    public function findValidToken(string $email, string $token): ?array
    {
        foreach ($this->all() as $item) {
            if (
                ($item['email'] ?? null) === $email &&
                ($item['token'] ?? null) === $token &&
                ($item['used_at'] ?? null) === null &&
                strtotime((string) ($item['expires_at'] ?? '')) >= time()
            ) {
                return $item;
            }
        }

        return null;
    }

    public function markUsed(string $email, string $token): void
    {
        $items = $this->all();
        foreach ($items as &$item) {
            if (($item['email'] ?? null) === $email && ($item['token'] ?? null) === $token) {
                $item['used_at'] = date(DATE_ATOM);
            }
        }

        Storage::write('password_resets.json', $items);
    }

    private function all(): array
    {
        return Storage::read('password_resets.json', []);
    }
}
