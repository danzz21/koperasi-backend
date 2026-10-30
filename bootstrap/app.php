<?php

use App\Http\Middleware\EnsureProfileCompleted;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        apiPrefix: 'api',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // CORS — semua API request boleh dari frontend React
        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        // Exclude API routes dari CSRF verification (gunakan Sanctum token instead)
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        // Alias middleware
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'profile.completed' => EnsureProfileCompleted::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Return JSON untuk semua exception di API
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->is('api/*')) {
                if ($e instanceof AuthenticationException) {
                    return response()->json(['message' => 'Unauthenticated.'], 401);
                }
                if ($e instanceof ValidationException) {
                    return response()->json([
                        'message' => 'Validasi gagal.',
                        'errors' => $e->errors(),
                    ], 422);
                }
                if ($e instanceof NotFoundHttpException) {
                    return response()->json(['message' => 'Endpoint tidak ditemukan.'], 404);
                }
            }
        });
    })->create();
