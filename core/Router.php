<?php

namespace Core;

class Router
{
    private array $routes = [];
    private string $prefix = '';

    /**
     * Register a GET route.
     */
    public function get(string $path, string $handler): self
    {
        $this->addRoute('GET', $path, $handler);
        return $this;
    }

    /**
     * Register a POST route.
     */
    public function post(string $path, string $handler): self
    {
        $this->addRoute('POST', $path, $handler);
        return $this;
    }

    /**
     * Register a route for any HTTP method.
     */
    public function any(string $path, string $handler): self
    {
        $this->addRoute('GET', $path, $handler);
        $this->addRoute('POST', $path, $handler);
        return $this;
    }

    /**
     * Set a prefix for subsequent routes.
     */
    public function prefix(string $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Dispatch the current request to the matching route.
     */
    public function dispatch(string $method, string $uri): void
    {
        // Strip query string
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = rtrim($path, '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = $this->buildPattern($route['path']);
            if (preg_match($pattern, $path, $matches)) {
                // Filter out numeric keys
                $params = array_filter($matches, fn($k) => !is_int($k), ARRAY_FILTER_USE_KEY);
                $this->callHandler($route['handler'], $params);
                return;
            }
        }

        http_response_code(404);
        require __DIR__ . '/../app/Views/errors/404.php';
    }

    private function addRoute(string $method, string $path, string $handler): void
    {
        $fullPath = $this->prefix . $path;
        $this->routes[] = [
            'method'  => $method,
            'path'    => $fullPath,
            'handler' => $handler,
        ];
    }

    /**
     * Convert a route path like "/medicines/{id}" into a regex pattern.
     */
    private function buildPattern(string $path): string
    {
        // Escape regex metacharacters in literal parts, then replace {param} with named capture groups
        $escaped = preg_quote($path, '#');
        // preg_quote escapes { and }, so we need to unescape our {param} placeholders
        $pattern = preg_replace('/\\\{(\w+)\\\}/', '(?P<$1>[^/]+)', $escaped);
        return '#^' . $pattern . '$#';
    }

    /**
     * Call the controller@method handler.
     */
    private function callHandler(string $handler, array $params): void
    {
        [$controllerClass, $method] = explode('@', $handler);

        $class = 'App\\Controllers\\' . $controllerClass;

        if (!class_exists($class)) {
            throw new \RuntimeException("Controller class {$class} not found.");
        }

        $controller = new $class();

        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Method {$method} not found in {$class}.");
        }

        call_user_func_array([$controller, $method], $params);
    }

    /**
     * Get all registered routes (useful for debugging).
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
