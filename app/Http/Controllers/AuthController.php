<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\AuthTokenData;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $tokenData = $this->authService->register($request->validated());

        return $this->respondWithToken($tokenData, 'Registration successful.', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $tokenData = $this->authService->login($request->only('email', 'password'));

        return $this->respondWithToken($tokenData, 'Login successful.');
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(
            ['user' => new UserResource($request->user())],
            'User retrieved successfully.',
        );
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return ApiResponse::success(null, 'Successfully logged out.');
    }

    private function respondWithToken(AuthTokenData $tokenData, string $message, int $code = 200): JsonResponse
    {
        return ApiResponse::success([
            'access_token' => $tokenData->accessToken,
            'user' => new UserResource($tokenData->user),
        ], $message, $code);
    }
}
