<?php

declare(strict_types=1);

namespace Orbit\Finance\Support;

final class ApiKernel
{
    public function handle(): void
    {
        $routes = require dirname(__DIR__, 2) . '/routes/api.php';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        foreach ($routes as [$routeMethod, $routePath, $handler]) {
            $params = $this->matchRoute($method, $path, $routeMethod, $routePath);
            if ($params !== null) {
                $controller = new $handler[0]();
                $action = $handler[1];
                $controller->{$action}($this->requestPayload($params));
                return;
            }
        }

        Http::json(['message' => 'Not Found'], 404);
    }

    private function requestPayload(array $routeParams = []): array
    {
        $input = file_get_contents('php://input');
        $payload = [];
        if (is_string($input) && $input !== '') {
            $decoded = json_decode($input, true);
            $payload = is_array($decoded) ? $decoded : [];
        }

        return array_merge($payload, $routeParams);
    }

    private function matchRoute(string $method, string $path, string $routeMethod, string $routePath): ?array
    {
        if ($method !== $routeMethod) {
            return null;
        }

        $pattern = preg_replace('#\{[^\}]+\}#', '([^/]+)', $routePath);
        if (!is_string($pattern)) {
            return null;
        }

        $pattern = '#^' . $pattern . '$#';
        if (!preg_match($pattern, $path, $matches)) {
            return null;
        }

        preg_match_all('#\{([^\}]+)\}#', $routePath, $names);
        $params = [];
        foreach ($names[1] as $index => $name) {
            $params[$name] = $matches[$index + 1] ?? null;
        }

        return $params;
    }
}
