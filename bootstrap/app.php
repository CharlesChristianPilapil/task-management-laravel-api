<?php

use App\Exceptions\ApiException;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\VerifyInternalServiceKey;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn () => null);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'active' => EnsureUserIsActive::class,
            'internal' => VerifyInternalServiceKey::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ApiException $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $exception->render();
            }
        });

        $exceptions->render(function (ValidationException $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error('The given data was invalid.', 422, $exception->errors());
            }
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $message = 'Authentication is required to access this resource.';
                return ApiResponse::error($message, 401);
            }
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $previous = $exception->getPrevious();

                if ($previous instanceof ModelNotFoundException) {
                    $message = match (class_basename($previous->getModel())) {
                        'User' => 'User does not exist.',
                        'Task' => 'Task does not exist.',
                        'Team' => 'Team does not exist.',
                        default => 'Resource not found.',
                    };

                    return ApiResponse::error($message, 404);
                }

                $message = 'The requested endpoint does not exist.';
                return ApiResponse::error($message, 404);
            }
        });

        $exceptions->render(function (MethodNotAllowedHttpException $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $message = 'The HTTP method used is not allowed for this endpoint.';
                return ApiResponse::error($message, 405);
            }
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (($request->is('api/*') || $request->expectsJson()) && ! app()->hasDebugModeEnabled()) {
                $message = 'An unexpected error occurred. Please try again later.';
                return ApiResponse::error($message, 500);
            }
        });
    })->create();
