<?php

declare(strict_types=1);

namespace Orbit\Finance\Http\Requests\Auth;

use Orbit\Finance\Support\Validator;

final class ForgotPasswordRequest
{
    public function validate(array $input): array
    {
        $error = Validator::email($input['email'] ?? null);

        if ($error !== null) {
            throw new \InvalidArgumentException(json_encode(['email' => [$error]], JSON_UNESCAPED_UNICODE));
        }

        return ['email' => strtolower(trim((string) $input['email']))];
    }
}
