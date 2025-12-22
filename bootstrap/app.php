<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ModeratorMiddleware;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('admin')
                ->group(base_path('routes/admin.php'));
            Route::middleware('web')
                ->prefix('moderator')
                ->group(base_path('routes/moderator.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register middleware aliases
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'moderator' => ModeratorMiddleware::class,
            'role' => RoleMiddleware::class,
            'user.status' => \App\Http\Middleware\CheckUserStatus::class,
            'bot.protection' => \App\Http\Middleware\BotProtectionMiddleware::class,
        ]);

        // Apply bot protection globally to web routes
        $middleware->web(append: [
            \App\Http\Middleware\BotProtectionMiddleware::class,
        ]);

        // TODO:  add user.status to global middleware stack
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
