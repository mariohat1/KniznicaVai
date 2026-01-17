<?php

namespace App\Support;

/**
 * Trait AuthHelper
 * Uses the application's authenticator directly and defensively.
 *
 * @property \Framework\Core\App $app
 */
trait AuthHelper
{
    /**
     * Return true if current authenticated principal has role 'admin'.
     */
    protected function isAdmin(): bool
    {
        try {
            $auth = $this->app->getAuth();
            if (!$auth) return false;
            if (!$auth->isLogged()) return false;
            $user = $auth->getUser();
            if (!is_object($user)) return false;
            $role = $user->getRole();
            return strtolower((string)$role) === 'admin';
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Return true if current principal is a logged-in non-admin user.
     */
    protected function isUser(): bool
    {
        try {
            $auth = $this->app->getAuth();
            if (!$auth) return false;
            if (!$auth->isLogged()) return false;
            $user = $auth->getUser();
            $role = $user->getRole();
            return strtolower((string)$role) === 'user';
        } catch (\Throwable $e) {
            return false;
        }
    }
}
