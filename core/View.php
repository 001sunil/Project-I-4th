<?php

namespace Core;

/**
 * View helper class.
 * Provides static methods for rendering common UI components.
 */
class View
{
    /**
     * Render a flash message alert.
     */
    public static function alert(string $type, ?string $message): void
    {
        if (empty($message)) return;

        $icon = match($type) {
            'success' => 'fa-circle-check',
            'danger'  => 'fa-circle-exclamation',
            'warning' => 'fa-triangle-exclamation',
            'info'    => 'fa-circle-info',
            default   => 'fa-circle-info',
        };

        echo '<div class="alert alert-' . htmlspecialchars($type) . '">';
        echo '<i class="fas ' . $icon . '"></i> ' . htmlspecialchars($message);
        echo '</div>';
    }

    /**
     * Render a CSRF hidden input field.
     */
    public static function csrfField(): void
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
    }

    /**
     * Get the current page name for active menu highlighting.
     */
    public static function currentPage(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
        $segments = array_filter(explode('/', $path));
        return end($segments) ?: 'dashboard';
    }

    /**
     * Check if a page is active for menu highlighting.
     */
    public static function isActive(string ...$pages): bool
    {
        $current = self::currentPage();
        return in_array($current, $pages, true);
    }

    /**
     * Sanitize output.
     */
    public static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public static function url(string $path = ''): string
    {
        return (defined('BASE_URL') ? BASE_URL : '') . $path;
    }
}
