<?php

declare(strict_types=1);

namespace Orbit\Finance\Repositories\Auth;

use Orbit\Finance\Support\Storage;

final class UserRepository
{
    public function findByEmail(string $email): ?array
    {
        foreach ($this->all() as $user) {
            if (($user['email'] ?? null) === $email) {
                return $user;
            }
        }

        return null;
    }

    public function create(array $data): array
    {
        $users = $this->all();
        $user = [
            'id' => $this->nextId($users),
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'created_at' => date(DATE_ATOM),
            'updated_at' => date(DATE_ATOM),
        ];

        $users[] = $user;
        Storage::write('users.json', $users);

        return $user;
    }

    private function all(): array
    {
        return Storage::read('users.json', []);
    }

    private function nextId(array $users): int
    {
        $ids = array_column($users, 'id');
        return $ids === [] ? 1 : (max($ids) + 1);
    }
}
