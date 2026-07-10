<?php

declare(strict_types=1);

namespace Orbit\Finance\Http\Requests\Auth;

use Orbit\Finance\Support\Validator;

final class LoginRequest
{
    public function validate(array $input): array
    {
        $errors = [];

        foreach ([
            'email' => fn ($value) => Validator::email($value),
            'password' => fn ($value) => Validator::requiredString($value, 'password', 1),
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
            'email' => strtolower(trim((string) $input['email'])),
            'password' => (string) $input['password'],
        ];
    }
}
