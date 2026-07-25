<?php

declare(strict_types=1);

namespace Orbit\Finance\Http\Controllers\Transaction;

use Orbit\Finance\Http\Requests\Transaction\StoreTransactionRequest;
use Orbit\Finance\Http\Requests\Transaction\UpdateTransactionRequest;
use Orbit\Finance\Services\Transaction\TransactionService;
use Orbit\Finance\Support\Http;
use Orbit\Finance\Support\TokenManager;

final class TransactionController
{
    public function __construct(
        private readonly TransactionService $service = new TransactionService(),
        private readonly TokenManager $tokens = new TokenManager(),
    ) {
    }

    public function index(array $input): void
    {
        try {
            $userEmail = $this->authorizedUserEmail();
            Http::json([
                'data' => $this->service->list($userEmail),
                'message' => 'Success',
            ]);
        } catch (\RuntimeException $exception) {
            Http::json(['message' => $exception->getMessage()], $exception->getCode() ?: 500);
        }
    }

    public function show(array $input): void
    {
        try {
            $userEmail = $this->authorizedUserEmail();
            $id = $this->extractId($input);
            $transaction = $this->service->show($userEmail, $id);

            if ($transaction === null) {
                Http::json(['message' => 'Transaction not found'], 404);
                return;
            }

            Http::json(['data' => $transaction, 'message' => 'Success']);
        } catch (\RuntimeException $exception) {
            Http::json(['message' => $exception->getMessage()], $exception->getCode() ?: 500);
        }
    }

    public function store(array $input): void
    {
        try {
            $userEmail = $this->authorizedUserEmail();
            $payload = (new StoreTransactionRequest())->validate($input);
            $transaction = $this->service->create($userEmail, $payload);

            Http::json([
                'data' => $transaction->toArray(),
                'message' => 'Transaction created successfully',
            ], 201);
        } catch (\InvalidArgumentException $exception) {
            Http::json([
                'message' => 'Validation failed',
                'errors' => json_decode($exception->getMessage(), true) ?: [],
            ], 422);
        } catch (\RuntimeException $exception) {
            Http::json(['message' => $exception->getMessage()], $exception->getCode() ?: 500);
        }
    }

    public function update(array $input): void
    {
        try {
            $userEmail = $this->authorizedUserEmail();
            $id = $this->extractId($input);
            $payload = (new UpdateTransactionRequest())->validate($input);
            $transaction = $this->service->update($userEmail, $id, $payload);

            if ($transaction === null) {
                Http::json(['message' => 'Transaction not found'], 404);
                return;
            }

            Http::json([
                'data' => $transaction->toArray(),
                'message' => 'Transaction updated successfully',
            ]);
        } catch (\InvalidArgumentException $exception) {
            Http::json([
                'message' => 'Validation failed',
                'errors' => json_decode($exception->getMessage(), true) ?: [],
            ], 422);
        } catch (\RuntimeException $exception) {
            Http::json(['message' => $exception->getMessage()], $exception->getCode() ?: 500);
        }
    }

    public function updateCategory(array $input): void
    {
        try {
            $userEmail = $this->authorizedUserEmail();
            $id = $this->extractId($input);
            $categoryId = $this->extractCategoryId($input);
            $transaction = $this->service->updateCategory($userEmail, $id, $categoryId);

            if ($transaction === null) {
                Http::json(['message' => 'Transaction not found'], 404);
                return;
            }

            Http::json([
                'data' => [
                    'transaction_id' => $transaction->id,
                    'category_id' => $transaction->categoryId,
                    'category_name' => $transaction->categoryName,
                ],
                'message' => 'Transaction category updated successfully',
            ]);
        } catch (\RuntimeException $exception) {
            Http::json(['message' => $exception->getMessage()], $exception->getCode() ?: 500);
        }
    }

    public function destroy(array $input): void
    {
        try {
            $userEmail = $this->authorizedUserEmail();
            $id = $this->extractId($input);
            $deleted = $this->service->delete($userEmail, $id);

            if (!$deleted) {
                Http::json(['message' => 'Transaction not found'], 404);
                return;
            }

            Http::json(['message' => 'Transaction deleted successfully']);
        } catch (\RuntimeException $exception) {
            Http::json(['message' => $exception->getMessage()], $exception->getCode() ?: 500);
        }
    }

    public function summary(array $input): void
    {
        try {
            $userEmail = $this->authorizedUserEmail();
            Http::json([
                'data' => $this->service->summary($userEmail)->toArray(),
                'message' => 'Success',
            ]);
        } catch (\RuntimeException $exception) {
            Http::json(['message' => $exception->getMessage()], $exception->getCode() ?: 500);
        }
    }

    private function authorizedUserEmail(): string
    {
        $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['Authorization'] ?? '';
        if (!is_string($authorization) || !str_starts_with($authorization, 'Bearer ')) {
            throw new \RuntimeException('access token missing', 401);
        }

        $token = trim(substr($authorization, 7));
        $payload = $this->tokens->parse($token);
        if ($payload === null || ($payload['type'] ?? null) !== 'access') {
            throw new \RuntimeException('invalid access token', 401);
        }

        return (string) $payload['sub'];
    }

    private function extractId(array $input): int
    {
        $id = $input['transaction'] ?? $input['id'] ?? $_GET['id'] ?? null;
        if (!is_numeric($id) || (int) $id <= 0) {
            throw new \RuntimeException('transaction id is required', 422);
        }

        return (int) $id;
    }

    private function extractCategoryId(array $input): int
    {
        $categoryId = $input['category_id'] ?? null;
        if (!is_numeric($categoryId) || (int) $categoryId <= 0) {
            throw new \RuntimeException('category id is required', 422);
        }

        return (int) $categoryId;
    }
}
