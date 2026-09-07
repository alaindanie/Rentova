<?php

namespace App\Core;

/**
 * Protection CSRF : génération et vérification des jetons.
 */
class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION[CSRF_KEY])) {
            $_SESSION[CSRF_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_KEY];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . e(self::token()) . '">';
    }

    public static function verify(?string $token): bool
    {
        return is_string($token) && hash_equals($_SESSION[CSRF_KEY] ?? '', $token);
    }
}
