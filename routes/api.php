<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Project\ProjectController;
use App\Http\Controllers\Api\Quota\QuotaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['status' => 'ok']);
});

Route::prefix('auth')->middleware(['throttle:30,1', \App\Http\Middleware\CapacityLimitMiddleware::class])->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    
    // Password Reset Routes
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('forgot-password/verify', [AuthController::class, 'verifyResetCode']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('auth:api')->group(function () {
        Route::middleware([\App\Http\Middleware\EnsureJwtClaimsMatch::class])->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            
            Route::get('user/request-history', [\App\Http\Controllers\Api\RequestLogController::class, 'index']);
            
            Route::get('credits/balance', [\App\Http\Controllers\Api\CreditController::class, 'balance']);
            Route::get('credits/history', [\App\Http\Controllers\Api\CreditController::class, 'history']);
            
            Route::post('payments/checkout', [\App\Http\Controllers\Api\PaymentController::class, 'checkout']);
            
            Route::apiResource('projects', ProjectController::class);
            Route::post('projects/{project}/restore', [ProjectController::class, 'restore']);
            Route::delete('projects/{project}/force', [ProjectController::class, 'forceDelete']);
            
            // Business routes
            Route::get('projects/{projectId}/business', [\App\Http\Controllers\Api\Business\BusinessController::class, 'show']);
            Route::post('projects/{projectId}/business', [\App\Http\Controllers\Api\Business\BusinessController::class, 'store']);
            Route::put('projects/{projectId}/business', [\App\Http\Controllers\Api\Business\BusinessController::class, 'update']);
            
            // Product routes
            Route::get('projects/{projectId}/business/products', [\App\Http\Controllers\Api\Product\ProductController::class, 'index']);
            Route::get('projects/{projectId}/business/products/{productId}', [\App\Http\Controllers\Api\Product\ProductController::class, 'show']);
            Route::post('projects/{projectId}/business/products', [\App\Http\Controllers\Api\Product\ProductController::class, 'store']);
            Route::put('projects/{projectId}/business/products/{productId}', [\App\Http\Controllers\Api\Product\ProductController::class, 'update']);
            Route::delete('projects/{projectId}/business/products/{productId}', [\App\Http\Controllers\Api\Product\ProductController::class, 'destroy']);
            
            // Product Media routes
            Route::get('projects/{projectId}/business/products/{productId}/media', [\App\Http\Controllers\Api\Product\ProductController::class, 'mediaIndex']);
            Route::post('projects/{projectId}/business/products/{productId}/media', [\App\Http\Controllers\Api\Product\ProductController::class, 'mediaStore']);
            Route::delete('projects/{projectId}/business/products/{productId}/media/{mediaId}', [\App\Http\Controllers\Api\Product\ProductController::class, 'mediaDestroy']);
            
            Route::get('quota', [QuotaController::class, 'show']);
            Route::put('quota', [QuotaController::class, 'update']);
            
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });
});

Route::post('webhooks/lemonsqueezy', [\App\Http\Controllers\Api\PaymentController::class, 'webhook']);

Route::fallback(function () {
    return response()->json([
        'status' => 'error',
    ], 404);
});