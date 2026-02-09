<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
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
        // Tüm hatalar JSON olarak dönsün (API ve web dahil).
        $exceptions->shouldRenderJsonWhen(fn () => true);

        // Hem exception hem response status kodu üzerinden JSON hata döndür.
        $exceptions->render(function (\Throwable $e, $request) {
            $status = 500;
            $message = 'Something went wrong.';

            if ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                $status = 429;
                $message = 'Too many requests. Please try again later.';
            } elseif (
                (class_exists(\Illuminate\Foundation\Http\Exceptions\MaintenanceModeException::class) && $e instanceof \Illuminate\Foundation\Http\Exceptions\MaintenanceModeException) ||
                (class_exists(\Illuminate\Foundation\Http\MaintenanceModeBypassCookie::class) && $e instanceof \Illuminate\Foundation\Http\MaintenanceModeBypassCookie)
            ) {
                $status = 503;
                $message = 'Service is currently under maintenance. Please try again later.';
            } elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                $status = 404;
                $message = 'Route not found';
            } elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                $status = 405;
                $message = 'Method not allowed';
            } elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                $status = $e->getStatusCode();
                $message = $e->getMessage() ?: $message;
            }

            if (method_exists($e, 'getStatusCode')) {
                $bodyStatus = $e->getStatusCode();
                if (!empty($bodyStatus) && is_int($bodyStatus) && $bodyStatus !== $status) {
                    $status = $bodyStatus;
                }
            }
            if (method_exists($e, 'getMessage')) {
                $bodyMessage = $e->getMessage();
                if (!empty($bodyMessage)) {
                    $message = $bodyMessage;
                }
            }

            return response()->json([
                'status' => 'error',
                'message' => $message,
                'exception' => config('app.debug') ? get_class($e) : null,
                'trace' => config('app.debug') ? $e->getTrace() : null,
                'code' => $status,
            ], $status);
        });

        // İsteğe bağlı, özel bir raporlama logic'iniz varsa aşağıya taşıyınız.
        // $exceptions->report(function (\Throwable $e) {
        //     // Report logic if necessary
        // });
    })
    ->create();
