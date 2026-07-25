<?php

declare(strict_types=1);

namespace Orbit\Finance\Services\Transaction;

use Orbit\Finance\Models\Transaction;
use Orbit\Finance\Repositories\Transaction\TransactionRepository;

final class TransactionService
{
    public function __construct(
        private readonly TransactionRepository $repository = new TransactionRepository(),
    ) {
    }

    public function list(string $userEmail): array
    {
        return array_map(static fn (array $item): array => $item, $this->repository->allForUser($userEmail));
    }

    public function create(string $userEmail, array $data): Transaction
    {
        return $this->repository->create($userEmail, $data);
    }
}
