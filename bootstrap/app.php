<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, $request) {
            if (!$request->is('api/*')) {
                return null; // let Laravel handle non-API routes normally
            }

            // 1. Validation errors -> 422 with field errors
            if ($e instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }

            // 2. Auth errors -> 401
            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please provide a valid token.',
                ], 401);
            }

            // 3. Any other HTTP exception (404, 403, 405, etc.) -> use its real status code
            if ($e instanceof HttpExceptionInterface) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Request failed',
                ], $e->getStatusCode());
            }

            // 4. Anything else -> 500
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Something went wrong',
            ], 500);
        });
    })
    ->create();
