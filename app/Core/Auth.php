<?php

namespace App\Core;

/**
 * Authentification & contrôle d'accès basés sur la session.
 */
class Auth
{
    public static function login(array $user): void
    {
        Session::regenerate();
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user']    = $user;
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['user']['role'] ?? null;
    }

    public static function is(string $role): bool
    {
        return self::role() === $role;
    }

    public static function isAgentOrResponsable(): bool
    {
        return in_array(self::role(), ['agent', 'responsable'], true);
    }

    public static function name(): string
    {
        $u = self::user();
        return $u ? trim(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? '')) : '';
    }

    public static function refresh(): void
    {
        if (!self::check()) {
            return;
        }
        $user = \App\Models\Utilisateur::find(self::id());
        if ($user) {
            unset($user['password']);
            Session::set('user', $user);
        }
    }
}
