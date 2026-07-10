<?php

declare(strict_types=1);

namespace Orbit\Finance\Support;

use Orbit\Finance\Http\Controllers\Auth\AuthController;
use Orbit\Finance\Http\Requests\Auth\ForgotPasswordRequest;
use Orbit\Finance\Http\Requests\Auth\LoginRequest;
use Orbit\Finance\Http\Requests\Auth\RegisterRequest;
use Orbit\Finance\Http\Requests\Auth\ResetPasswordRequest;

final class ApiKernel
{
    public function handle(): void
    {
        $routes = require dirname(__DIR__, 2) . '/routes/api.php';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        foreach ($routes as [$routeMethod, $routePath, $handler]) {
            if ($method === $routeMethod && $path === $routePath) {
                $controller = new $handler[0]();
                $action = $handler[1];
                $controller->{$action}($this->requestPayload());
                return;
            }
        }

        Http::json(['message' => 'Not Found'], 404);
    }

    private function requestPayload(): array
    {
        $input = file_get_contents('php://input');
        if (!is_string($input) || $input === '') {
            return [];
        }

        $decoded = json_decode($input, true);
        return is_array($decoded) ? $decoded : [];
    }
}
