<?php

declare(strict_types=1);

namespace Orbit\Finance\Http\Requests\Auth;

use Orbit\Finance\Support\Validator;

final class ResetPasswordRequest
{
    public function validate(array $input): array
    {
        $errors = [];

        foreach ([
            'token' => fn ($value) => Validator::requiredString($value, 'token', 1),
            'email' => fn ($value) => Validator::email($value),
            'password' => fn ($value) => Validator::requiredString($value, 'password', 8),
            'password_confirmation' => fn ($value) => Validator::requiredString($value, 'password_confirmation', 8),
        ] as $field => $rule) {
            $error = $rule($input[$field] ?? null);
            if ($error !== null) {
                $errors[$field][] = $error;
            }
        }

        if (($input['password'] ?? null) !== ($input['password_confirmation'] ?? null)) {
            $errors['password_confirmation'][] = 'password_confirmation must match password.';
        }

        if ($errors !== []) {
            throw new \InvalidArgumentException(json_encode($errors, JSON_UNESCAPED_UNICODE));
        }

        return [
            'token' => (string) $input['token'],
            'email' => strtolower(trim((string) $input['email'])),
            'password' => (string) $input['password'],
        ];
    }
}
