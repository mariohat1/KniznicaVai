<?php

namespace App\Support;

/**
 * Small view helper to centralize common auth checks and display info for views/layouts.
 * Keeps controllers and views DRY without touching framework core.
 */
class AuthView
{
    /**
     * Returns true if current user should be allowed to add authors (admin role check).
     * Accepts any authenticator object that provides isLogged() and getUser().
     *
     * @param mixed $auth
     * @return bool
     */
    public static function canAddAuthor(mixed $auth): bool
    {
        try {
            if (!isset($auth) || !$auth?->isLogged()) {
                return false;
            }

            $user = $auth->getUser();
            if (!is_object($user)) {
                return false;
            }

            if (method_exists($user, 'getRole')) {
                return (strtolower((string)$user->getRole()) === 'admin');
            }
            if (isset($user->role)) {
                return (strtolower((string)$user->role) === 'admin');
            }

            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Friendly display name for the current user (not escaped).
     */
    public static function displayName(mixed $auth): string
    {
        try {
            $user = $auth?->getUser();
            if (!is_object($user)) return '';

            if (method_exists($user, 'getName')) return (string)$user->getName();
            if (method_exists($user, 'getUsername')) return (string)$user->getUsername();

            $vars = get_object_vars($user);
            return (string)($vars['name'] ?? $vars['username'] ?? '');
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * HTML-escaped display name.
     */
    public static function displayNameEsc(mixed $auth): string
    {
        return htmlspecialchars(self::displayName($auth));
    }
}

