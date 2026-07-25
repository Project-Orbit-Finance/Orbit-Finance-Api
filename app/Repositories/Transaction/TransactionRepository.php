<?php

declare(strict_types=1);

namespace Orbit\Finance\Repositories\Transaction;

use Orbit\Finance\Models\Transaction;
use Orbit\Finance\Support\Storage;

final class TransactionRepository
{
    public function allForUser(string $userEmail): array
    {
        return array_values(array_filter($this->all(), static fn (array $item): bool => ($item['user_email'] ?? null) === $userEmail));
    }

    public function create(string $userEmail, array $data): Transaction
    {
        $items = $this->all();
        $transaction = new Transaction(
            id: $this->nextId($items),
            userEmail: $userEmail,
            description: $data['description'],
            type: $data['type'],
            amount: $data['amount'],
            date: $data['date'],
            accountId: $data['account_id'] ?? null,
            accountName: $data['account_name'] ?? null,
            categoryId: $data['category_id'] ?? null,
            categoryName: $data['category_name'] ?? null,
            notes: $data['notes'] ?? null,
            createdAt: date(DATE_ATOM),
            updatedAt: date(DATE_ATOM),
        );

        $items[] = $transaction->toArray();
        Storage::write('transactions.json', $items);

        return $transaction;
    }

    public function update(string $userEmail, int $id, array $data): ?Transaction
    {
        $items = $this->all();
        $updated = null;

        foreach ($items as &$item) {
            if ((int) ($item['id'] ?? 0) === $id && ($item['user_email'] ?? null) === $userEmail) {
                $item = array_merge($item, [
                    'description' => $data['description'],
                    'type' => $data['type'],
                    'amount' => $data['amount'],
                    'date' => $data['date'],
                    'account_id' => $data['account_id'] ?? null,
                    'account_name' => $data['account_name'] ?? null,
                    'category_id' => $data['category_id'] ?? null,
                    'category_name' => $data['category_name'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'updated_at' => date(DATE_ATOM),
                ]);

                $updated = new Transaction(
                    id: (int) $item['id'],
                    userEmail: (string) $item['user_email'],
                    description: (string) $item['description'],
                    type: (string) $item['type'],
                    amount: (float) $item['amount'],
                    date: (string) $item['date'],
                    accountId: isset($item['account_id']) ? (int) $item['account_id'] : null,
                    accountName: isset($item['account_name']) ? (string) $item['account_name'] : null,
                    categoryId: isset($item['category_id']) ? (int) $item['category_id'] : null,
                    categoryName: isset($item['category_name']) ? (string) $item['category_name'] : null,
                    notes: isset($item['notes']) ? (string) $item['notes'] : null,
                    createdAt: (string) ($item['created_at'] ?? date(DATE_ATOM)),
                    updatedAt: (string) $item['updated_at'],
                );
                break;
            }
        }

        Storage::write('transactions.json', $items);

        return $updated;
    }

    public function delete(string $userEmail, int $id): bool
    {
        $items = $this->all();
        $before = count($items);
        $items = array_values(array_filter($items, static fn (array $item): bool => !((int) ($item['id'] ?? 0) === $id && ($item['user_email'] ?? null) === $userEmail)));
        Storage::write('transactions.json', $items);

        return count($items) < $before;
    }

    public function findByIdForUser(string $userEmail, int $id): ?array
    {
        foreach ($this->all() as $item) {
            if ((int) ($item['id'] ?? 0) === $id && ($item['user_email'] ?? null) === $userEmail) {
                return $item;
            }
        }

        return null;
    }

    private function all(): array
    {
        return Storage::read('transactions.json', []);
    }

    private function nextId(array $items): int
    {
        $ids = array_column($items, 'id');
        return $ids === [] ? 1 : (max($ids) + 1);
    }
}
