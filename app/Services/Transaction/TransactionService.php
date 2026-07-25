<?php

declare(strict_types=1);

namespace Orbit\Finance\Services\Transaction;

use Orbit\Finance\Models\DashboardSummary;
use Orbit\Finance\Models\Transaction;
use Orbit\Finance\Repositories\Transaction\AccountRepository;
use Orbit\Finance\Repositories\Transaction\CategoryRepository;
use Orbit\Finance\Repositories\Transaction\TransactionRepository;

final class TransactionService
{
    public function __construct(
        private readonly TransactionRepository $repository = new TransactionRepository(),
        private readonly AccountRepository $accounts = new AccountRepository(),
        private readonly CategoryRepository $categories = new CategoryRepository(),
    ) {
    }

    public function list(string $userEmail): array
    {
        return array_map(static fn (array $item): array => $item, $this->repository->allForUser($userEmail));
    }

    public function show(string $userEmail, int $id): ?array
    {
        return $this->repository->findByIdForUser($userEmail, $id);
    }

    public function create(string $userEmail, array $data): Transaction
    {
        return $this->repository->create($userEmail, $this->enrichRelationships($data));
    }

    public function update(string $userEmail, int $id, array $data): ?Transaction
    {
        return $this->repository->update($userEmail, $id, $this->enrichRelationships($data));
    }

    public function delete(string $userEmail, int $id): bool
    {
        return $this->repository->delete($userEmail, $id);
    }

    public function updateCategory(string $userEmail, int $id, int $categoryId): ?Transaction
    {
        $current = $this->repository->findByIdForUser($userEmail, $id);
        if ($current === null) {
            return null;
        }

        $category = $this->categories->resolve($categoryId, null);
        return $this->repository->update($userEmail, $id, [
            'description' => (string) $current['description'],
            'type' => (string) $current['type'],
            'amount' => (float) $current['amount'],
            'date' => (string) $current['date'],
            'account_id' => isset($current['account_id']) ? (int) $current['account_id'] : null,
            'account_name' => isset($current['account_name']) ? (string) $current['account_name'] : null,
            'category_id' => $category['id'],
            'category_name' => $category['name'],
            'notes' => isset($current['notes']) ? (string) $current['notes'] : null,
        ]);
    }

    public function summary(string $userEmail): DashboardSummary
    {
        $items = $this->repository->allForUser($userEmail);
        $income = 0.0;
        $expenses = 0.0;

        foreach ($items as $item) {
            if (($item['type'] ?? null) === 'income') {
                $income += (float) ($item['amount'] ?? 0);
            } else {
                $expenses += (float) ($item['amount'] ?? 0);
            }
        }

        $balance = $income - $expenses;

        return new DashboardSummary(
            currentBalance: $balance,
            totalIncome: $income,
            totalExpenses: $expenses,
            monthlyVariation: $income - $expenses,
            goalProgress: 0.0,
            transactionCount: count($items),
        );
    }

    private function enrichRelationships(array $data): array
    {
        $account = $this->accounts->resolve($data['account_id'] ?? null, $data['account_name'] ?? null);
        $category = $this->categories->resolve($data['category_id'] ?? null, $data['category_name'] ?? null);

        $data['account_id'] = $account['id'];
        $data['account_name'] = $account['name'];
        $data['category_id'] = $category['id'];
        $data['category_name'] = $category['name'];

        return $data;
    }
}
