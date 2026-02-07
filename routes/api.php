<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware(['throttle:6,1', \App\Http\Middleware\CapacityLimitMiddleware::class])->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    
    // Password Reset Routes
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('forgot-password/verify', [AuthController::class, 'verifyResetCode']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('auth:api')->group(function () {
        // Routes protected by JWT + Anti-Hijacking
        Route::middleware([\App\Http\Middleware\EnsureJwtClaimsMatch::class])->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            
            Route::get('user/request-history', [\App\Http\Controllers\Api\RequestLogController::class, 'index']);
            
            // Credits
            Route::get('credits/balance', [\App\Http\Controllers\Api\CreditController::class, 'balance']);
            Route::get('credits/history', [\App\Http\Controllers\Api\CreditController::class, 'history']);
            
            // Payments
            Route::post('payments/checkout', [\App\Http\Controllers\Api\PaymentController::class, 'checkout']);
            
            // Auth Actions
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });
});

Route::post('webhooks/lemonsqueezy', [\App\Http\Controllers\Api\PaymentController::class, 'webhook']);
