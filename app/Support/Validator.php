<?php

declare(strict_types=1);

namespace Orbit\Finance\Support;

final class Validator
{
    public static function requiredString(mixed $value, string $field, int $minLength = 1): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return $field . ' is required.';
        }

        if (mb_strlen(trim($value)) < $minLength) {
            return $field . ' must be at least ' . $minLength . ' characters.';
        }

        return null;
    }

    public static function email(mixed $value): ?string
    {
        if (!is_string($value) || filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            return 'email must be a valid email address.';
        }

        return null;
    }
}
