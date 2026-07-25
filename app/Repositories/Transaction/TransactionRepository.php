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
            categoryId: $data['category_id'] ?? null,
            notes: $data['notes'] ?? null,
            createdAt: date(DATE_ATOM),
            updatedAt: date(DATE_ATOM),
        );

        $items[] = $transaction->toArray();
        Storage::write('transactions.json', $items);

        return $transaction;
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
