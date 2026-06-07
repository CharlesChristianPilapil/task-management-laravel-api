<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\DataTransferObjects\AuthTokenData;
use App\Enums\UserRole;
use App\Exceptions\ApiException;
use App\Models\User;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private JWTGuard $guard,
    ) {}

    public function register(array $data): AuthTokenData
    {
        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => UserRole::TeamMember,
            'is_active' => true,
        ]);

        return $this->issueToken($user);
    }

    public function login(array $credentials): AuthTokenData
    {
        if (! $token = $this->guard->attempt($credentials)) {
            throw ApiException::make('Invalid credentials.', 401);
        }

        $user = $this->guard->user();

        if (! $user instanceof User || ! $user->is_active) {
            $this->guard->logout();

            throw ApiException::make('Your account has been deactivated.', 403);
        }

        return new AuthTokenData(
            accessToken: $token,
            user: $user,
            expiresIn: $this->guard->factory()->getTTL() * 60,
        );
    }

    public function logout(): void
    {
        $this->guard->logout();
    }

    private function issueToken(User $user): AuthTokenData
    {
        $token = $this->guard->login($user);

        return new AuthTokenData(
            accessToken: $token,
            user: $user,
            expiresIn: $this->guard->factory()->getTTL() * 60,
        );
    }
}
