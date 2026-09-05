<?php

namespace Core;

/**
 * Simple middleware class for authentication and authorization.
 */
class Middleware
{
    /**
     * Require the user to be logged in.
     */
    public static function auth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }
    }

    /**
     * Require the user to have a specific role.
     */
    public static function role(string $requiredRole): void
    {
        self::auth();

        if (($_SESSION['role'] ?? '') !== $requiredRole) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/dashboard');
            exit;
        }
    }

    /**
     * Require the user to be an admin.
     */
    public static function admin(): void
    {
        self::role('admin');
    }

    /**
     * Guest-only middleware (redirect logged-in users away from login/register).
     */
    public static function guest(): void
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/dashboard');
            exit;
        }
    }
}
