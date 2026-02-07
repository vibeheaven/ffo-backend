<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \App\Http\Middleware\ForceJsonResponse::class,
            \App\Http\Middleware\LogHttpActivity::class,
            \App\Http\Middleware\SecureHeaders::class,
            \App\Http\Middleware\SanitizeInput::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function ($request, $e) {
            if ($request->is('api/*')) {
                return true;
            }

            return $request->expectsJson();
        });

        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Too many requests. Please try again later.',
                ], 429);
            }
        });

        $exceptions->render(function (\Illuminate\Foundation\Http\Exceptions\MaintenanceModeException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Service is currently under maintenance. Please try again later.',
                ], 503);
            }
        });

        $exceptions->report(function (Throwable $e) {
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException && $e->getStatusCode() === 500) {
                 \Illuminate\Support\Facades\Artisan::call('down');
            }
                if (!$e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                 \Illuminate\Support\Facades\Artisan::call('down');
            }
        });
    })->create();
