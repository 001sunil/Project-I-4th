<?php

namespace Core;

/**
 * Base Controller class.
 * All application controllers extend this.
 */
abstract class Controller
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Render a view with optional data.
     */
    protected function view(string $view, array $data = []): void
    {
        extract($data, EXTR_PREFIX_ALL, '_viewData');
        $viewPath = __DIR__ . '/../app/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View [{$view}] not found.");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Check if the view specifies a layout
        if (isset($_viewData_layout)) {
            $layoutPath = __DIR__ . '/../app/Views/layouts/' . $_viewData_layout . '.php';
            if (!file_exists($layoutPath)) {
                throw new \RuntimeException("Layout [{$_viewData_layout}] not found.");
            }
            require $layoutPath;
        } else {
            echo $content;
        }
    }

    /**
     * Redirect to a URL.
     */
    protected function redirect(string $url): void
    {
        if (strpos($url, 'http') !== 0 && defined('BASE_URL')) {
            $url = BASE_URL . $url;
        }
        header('Location: ' . $url);
        exit;
    }

    /**
     * Set a flash message in the session.
     */
    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Get and clear a flash message.
     */
    protected function getFlash(string $type): ?string
    {
        $message = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $message;
    }

    /**
     * Generate a CSRF token.
     */
    protected function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate a CSRF token.
     */
    protected function validateCsrf(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Check if the request is a POST.
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Get a POST value with optional default.
     */
    protected function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Get a GET value with optional default.
     */
    protected function query(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }
}
