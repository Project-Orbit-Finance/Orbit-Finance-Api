<?php

declare(strict_types=1);

namespace Orbit\Finance\Http\Requests\Transaction;

use Orbit\Finance\Support\Validator;

final class StoreTransactionRequest
{
    public function validate(array $input): array
    {
        $errors = [];

        foreach ([
            'description' => fn ($value) => Validator::requiredString($value, 'description', 2),
            'type' => fn ($value) => in_array($value, ['income', 'expense'], true) ? null : 'type must be income or expense.',
            'amount' => fn ($value) => is_numeric($value) && (float) $value > 0 ? null : 'amount must be a positive number.',
            'date' => fn ($value) => $this->validDate($value),
        ] as $field => $rule) {
            $error = $rule($input[$field] ?? null);
            if ($error !== null) {
                $errors[$field][] = $error;
            }
        }

        if ($errors !== []) {
            throw new \InvalidArgumentException(json_encode($errors, JSON_UNESCAPED_UNICODE));
        }

        return [
            'description' => trim((string) $input['description']),
            'type' => (string) $input['type'],
            'amount' => (float) $input['amount'],
            'date' => (string) $input['date'],
            'account_id' => isset($input['account_id']) ? (int) $input['account_id'] : null,
            'category_id' => isset($input['category_id']) ? (int) $input['category_id'] : null,
            'notes' => isset($input['notes']) ? trim((string) $input['notes']) : null,
        ];
    }

    private function validDate(mixed $value): ?string
    {
        if (!is_string($value) || $value === '') {
            return 'date is required.';
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) {
            return 'date must use Y-m-d format.';
        }

        return null;
    }
}
