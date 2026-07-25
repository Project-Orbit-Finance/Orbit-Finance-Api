<?php

declare(strict_types=1);

namespace Orbit\Finance\Http\Controllers\Transaction;

use Orbit\Finance\Http\Requests\Transaction\StoreTransactionRequest;
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
}
