<?php

declare(strict_types=1);

namespace Orbit\Finance\Services\Auth;

use Orbit\Finance\Repositories\Auth\PasswordResetRepository;
use Orbit\Finance\Repositories\Auth\SessionRepository;
use Orbit\Finance\Repositories\Auth\UserRepository;
use Orbit\Finance\Support\TokenManager;

final class AuthService
{
    public function __construct(
        private readonly UserRepository $users = new UserRepository(),
        private readonly SessionRepository $sessions = new SessionRepository(),
        private readonly PasswordResetRepository $passwordResets = new PasswordResetRepository(),
        private readonly TokenManager $tokens = new TokenManager(),
    ) {
    }

    public function register(array $data): array
    {
        if ($this->users->findByEmail($data['email']) !== null) {
            throw new \RuntimeException('email already exists', 409);
        }

        $user = $this->users->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
        ]);

        $accessToken = $this->tokens->issueAccessToken($user['email']);
        $refreshToken = $this->tokens->issueRefreshToken($user['email']);
        $this->sessions->create($user['email'], $accessToken, $refreshToken);

        return [
            'user' => $this->safeUser($user),
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }

    public function login(array $data): array
    {
        $user = $this->users->findByEmail($data['email']);
        if ($user === null || !password_verify($data['password'], (string) ($user['password'] ?? ''))) {
            throw new \RuntimeException('invalid credentials', 401);
        }

        $accessToken = $this->tokens->issueAccessToken($user['email']);
        $refreshToken = $this->tokens->issueRefreshToken($user['email']);
        $this->sessions->create($user['email'], $accessToken, $refreshToken);

        return [
            'user' => $this->safeUser($user),
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }

    public function logout(string $accessToken): void
    {
        $session = $this->sessions->findByAccessToken($accessToken);
        if ($session === null) {
            throw new \RuntimeException('session not found', 401);
        }

        $this->sessions->revokeByAccessToken($accessToken);
    }

    public function refresh(string $refreshToken): array
    {
        $payload = $this->tokens->parse($refreshToken);
        if ($payload === null || ($payload['type'] ?? null) !== 'refresh') {
            throw new \RuntimeException('invalid refresh token', 401);
        }

        $session = $this->sessions->findByRefreshToken($refreshToken);
        if ($session === null) {
            throw new \RuntimeException('session not found', 401);
        }

        $email = (string) $payload['sub'];
        $accessToken = $this->tokens->issueAccessToken($email);
        $newRefreshToken = $this->tokens->issueRefreshToken($email);
        $this->sessions->revokeByRefreshToken($refreshToken);
        $this->sessions->create($email, $accessToken, $newRefreshToken);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $newRefreshToken,
        ];
    }

    public function forgotPassword(string $email): void
    {
        $token = bin2hex(random_bytes(32));
        $this->passwordResets->create($email, $token);
    }

    public function resetPassword(array $data): void
    {
        $reset = $this->passwordResets->findValidToken($data['email'], $data['token']);
        if ($reset === null) {
            throw new \RuntimeException('invalid reset token', 400);
        }

        $user = $this->users->findByEmail($data['email']);
        if ($user === null) {
            throw new \RuntimeException('user not found', 404);
        }

        $updated = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'created_at' => $user['created_at'] ?? date(DATE_ATOM),
            'updated_at' => date(DATE_ATOM),
        ];

        $all = array_map(static function (array $item) use ($updated): array {
            return ($item['email'] ?? null) === $updated['email'] ? $updated : $item;
        }, \Orbit\Finance\Support\Storage::read('users.json', []));
        \Orbit\Finance\Support\Storage::write('users.json', $all);

        $this->passwordResets->markUsed($data['email'], $data['token']);
        $this->sessions->revokeByEmail($data['email']);
    }

    private function safeUser(array $user): array
    {
        return [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
        ];
    }
}
