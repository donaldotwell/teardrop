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
            Route::middleware('web')
                ->prefix('vendor')
                ->group(base_path('routes/vendor.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register middleware aliases
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'moderator' => ModeratorMiddleware::class,
            'role' => RoleMiddleware::class,
            'user.status' => \App\Http\Middleware\CheckUserStatus::class,
            'vendor.pgp' => \App\Http\Middleware\RequireVendorPgp::class,
            'bot.protection' => \App\Http\Middleware\BotProtectionMiddleware::class,
        ]);

        // Apply security middleware globally to web routes
        $middleware->web(append: [
            \App\Http\Middleware\BotProtectionMiddleware::class,
            \App\Http\Middleware\UpdateLastSeenAt::class,
            \App\Http\Middleware\CheckUserStatus::class, // Check user status (banned/inactive)
            \App\Http\Middleware\ShareViewData::class, // Share common data with views (once per request)
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Custom error page rendering
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response) {
            $statusCode = $response->getStatusCode();

            // List of status codes we have custom pages for
            $customPages = [400, 401, 402, 403, 404, 405, 408, 413, 419, 422, 429, 498, 500, 502, 503, 504];

            // If we have a custom page for this status code, use it
            if (in_array($statusCode, $customPages)) {
                $view = "errors.{$statusCode}";

                if (view()->exists($view)) {
                    return response()->view($view, [], $statusCode);
                }
            }

            // Fallback: render a generic error page for any other status code
            if ($statusCode >= 400) {
                $isServerError = $statusCode >= 500;

                return response()->view('errors.generic', [
                    'code' => $statusCode,
                    'message' => $isServerError ? 'Server Error' : 'Error',
                    'details' => $isServerError
                        ? 'The server encountered an error. Please try again later.'
                        : 'An error occurred while processing your request.',
                ], $statusCode);
            }

            return $response;
        });
    })->create();
