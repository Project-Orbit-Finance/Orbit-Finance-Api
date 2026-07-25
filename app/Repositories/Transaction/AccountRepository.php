<?php

declare(strict_types=1);

namespace Orbit\Finance\Repositories\Transaction;

use Orbit\Finance\Support\Storage;

final class AccountRepository
{
    public function resolve(?int $id, ?string $name): array
    {
        if ($id === null && $name === null) {
            return ['id' => null, 'name' => null];
        }

        $items = $this->all();
        foreach ($items as $item) {
            if ($id !== null && (int) ($item['id'] ?? 0) === $id) {
                return ['id' => $id, 'name' => (string) ($item['name'] ?? $name)];
            }

            if ($name !== null && ($item['name'] ?? null) === $name) {
                return ['id' => (int) ($item['id'] ?? 0), 'name' => $name];
            }
        }

        $resolved = [
            'id' => $id ?? $this->nextId($items),
            'name' => $name ?? ('Account ' . ($id ?? $this->nextId($items))),
        ];

        $items[] = $resolved;
        Storage::write('accounts.json', $items);

        return $resolved;
    }

    private function all(): array
    {
        return Storage::read('accounts.json', []);
    }

    private function nextId(array $items): int
    {
        $ids = array_column($items, 'id');
        return $ids === [] ? 1 : (max($ids) + 1);
    }
}
