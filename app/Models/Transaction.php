<?php

declare(strict_types=1);

namespace Orbit\Finance\Models;

final class Transaction
{
    public function __construct(
        public readonly int $id,
        public readonly string $userEmail,
        public readonly string $description,
        public readonly string $type,
        public readonly float $amount,
        public readonly string $date,
        public readonly ?int $accountId = null,
        public readonly ?string $accountName = null,
        public readonly ?int $categoryId = null,
        public readonly ?string $categoryName = null,
        public readonly ?string $notes = null,
        public readonly string $createdAt = '',
        public readonly string $updatedAt = '',
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_email' => $this->userEmail,
            'description' => $this->description,
            'type' => $this->type,
            'amount' => $this->amount,
            'date' => $this->date,
            'account_id' => $this->accountId,
            'account_name' => $this->accountName,
            'category_id' => $this->categoryId,
            'category_name' => $this->categoryName,
            'notes' => $this->notes,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
