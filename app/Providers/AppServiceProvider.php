<?php

namespace App\Providers;

use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Contracts\Repositories\TeamRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Repositories\TaskRepository;
use App\Repositories\TeamRepository;
use App\Repositories\UserRepository;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class,
        );

        $this->app->bind(
            TaskRepositoryInterface::class,
            TaskRepository::class,
        );

        $this->app->bind(
            TeamRepositoryInterface::class,
            TeamRepository::class,
        );

        $this->app->bind(
            JWTGuard::class,
            fn ($app) => $app['auth']->guard('api'),
        );
    }

    public function boot(UrlGenerator $url): void
    {
        if (env('APP_ENV') === 'production') {
            $url->forceScheme('https');
        }
    }
}
