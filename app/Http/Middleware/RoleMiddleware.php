<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return ApiResponse::error('Unauthenticated.', 401);
        }

        $allowedRoles = array_map(fn (string $role) => UserRole::from($role), $roles);

        if (! in_array($user->role, $allowedRoles, true)) {
            return ApiResponse::error('Forbidden.', 403);
        }

        return $next($request);
    }
}
