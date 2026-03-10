<?php

use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SetWebsiteTenantConnection;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function ($router) {
            require __DIR__ . '/../routes/admin.php';
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(SetWebsiteTenantConnection::class);
        $middleware->web(SetLocale::class);

        $middleware->redirectUsersTo(function () {
            if (checkIfAdmin()) {
                return '/admin/dashboard';
            }
            return '/';
        });
        $middleware->redirectGuestsTo('/');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'code' => 422,
                    'message' => 'Validation errors',
                    'data' => $e->errors(),
                ]);
            }
        });
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'code' => 401,
                    'message' => 'Unauthorized',
                    'data' => null
                ]);
            }
        });
    })->create();
