<?php

declare(strict_types=1);

namespace Orbit\Finance\Http\Controllers\Auth;

use Orbit\Finance\Http\Requests\Auth\ForgotPasswordRequest;
use Orbit\Finance\Http\Requests\Auth\LoginRequest;
use Orbit\Finance\Http\Requests\Auth\RegisterRequest;
use Orbit\Finance\Http\Requests\Auth\ResetPasswordRequest;
use Orbit\Finance\Services\Auth\AuthService;
use Orbit\Finance\Support\Http;
use Orbit\Finance\Support\TokenManager;

final class AuthController
{
    public function __construct(private readonly AuthService $service = new AuthService())
    {
    }

    public function register(array $input): void
    {
        try {
            $payload = (new RegisterRequest())->validate($input);
            $result = $this->service->register($payload);

            Http::json([
                'data' => [
                    'user' => $result['user'],
                    'access_token' => $result['access_token'],
                ],
                'message' => 'User registered successfully',
            ], 201, ['Set-Cookie' => $this->refreshCookie($result['refresh_token'])]);
        } catch (\InvalidArgumentException $exception) {
            Http::json([
                'message' => 'Validation failed',
                'errors' => json_decode($exception->getMessage(), true) ?: [],
            ], 422);
        } catch (\RuntimeException $exception) {
            Http::json(['message' => $exception->getMessage()], $exception->getCode() ?: 500);
        }
    }

    public function login(array $input): void
    {
        try {
            $payload = (new LoginRequest())->validate($input);
            $result = $this->service->login($payload);

            Http::json([
                'data' => [
                    'user' => $result['user'],
                    'access_token' => $result['access_token'],
                ],
                'message' => 'Login successful',
            ], 200, ['Set-Cookie' => $this->refreshCookie($result['refresh_token'])]);
        } catch (\InvalidArgumentException $exception) {
            Http::json([
                'message' => 'Validation failed',
                'errors' => json_decode($exception->getMessage(), true) ?: [],
            ], 422);
        } catch (\RuntimeException $exception) {
            Http::json(['message' => $exception->getMessage()], $exception->getCode() ?: 500);
        }
    }

    public function logout(array $input): void
    {
        try {
            $accessToken = $this->extractAccessToken();
            $this->service->logout($accessToken);
            http_response_code(204);
            header('Set-Cookie: ' . $this->clearRefreshCookie());
        } catch (\RuntimeException $exception) {
            Http::json(['message' => $exception->getMessage()], $exception->getCode() ?: 500);
        }
    }

    public function refresh(array $input): void
    {
        try {
            $refreshToken = $this->extractRefreshToken();
            $result = $this->service->refresh($refreshToken);

            Http::json([
                'data' => ['access_token' => $result['access_token']],
                'message' => 'Token refreshed successfully',
            ], 200, ['Set-Cookie' => $this->refreshCookie($result['refresh_token'])]);
        } catch (\RuntimeException $exception) {
            Http::json(['message' => $exception->getMessage()], $exception->getCode() ?: 500);
        }
    }

    public function forgotPassword(array $input): void
    {
        try {
            $payload = (new ForgotPasswordRequest())->validate($input);
            $this->service->forgotPassword($payload['email']);

            Http::json([
                'message' => 'If the email exists, a recovery link was sent',
            ], 200);
        } catch (\InvalidArgumentException $exception) {
            Http::json([
                'message' => 'Validation failed',
                'errors' => json_decode($exception->getMessage(), true) ?: [],
            ], 422);
        }
    }

    public function resetPassword(array $input): void
    {
        try {
            $payload = (new ResetPasswordRequest())->validate($input);
            $this->service->resetPassword($payload);

            Http::json(['message' => 'Password reset successfully'], 200);
        } catch (\InvalidArgumentException $exception) {
            Http::json([
                'message' => 'Validation failed',
                'errors' => json_decode($exception->getMessage(), true) ?: [],
            ], 422);
        } catch (\RuntimeException $exception) {
            Http::json(['message' => $exception->getMessage()], $exception->getCode() ?: 500);
        }
    }

    private function extractRefreshToken(): string
    {
        $cookie = $_COOKIE['refresh_token'] ?? '';
        if (!is_string($cookie) || $cookie === '') {
            throw new \RuntimeException('refresh token missing', 401);
        }

        return $cookie;
    }

    private function extractAccessToken(): string
    {
        $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['Authorization'] ?? '';
        if (!is_string($authorization) || !str_starts_with($authorization, 'Bearer ')) {
            throw new \RuntimeException('access token missing', 401);
        }

        $token = trim(substr($authorization, 7));
        if ($token === '') {
            throw new \RuntimeException('access token missing', 401);
        }

        return $token;
    }

    private function refreshCookie(string $token): string
    {
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? '; Secure' : '';

        return sprintf('refresh_token=%s; Path=/; HttpOnly; SameSite=Lax%s', urlencode($token), $secure);
    }

    private function clearRefreshCookie(): string
    {
        return 'refresh_token=; Path=/; HttpOnly; SameSite=Lax; Max-Age=0';
    }
}
