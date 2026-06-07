<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyInternalServiceKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $serviceKey = config('services.internal.service_key');
        $providedKey = $request->header('X-Service-Key');

        if (! is_string($serviceKey) || $serviceKey === '' || $providedKey !== $serviceKey) {
            return ApiResponse::error('Unauthorized internal request.', 401);
        }

        return $next($request);
    }
}
