<?php

use App\Http\Controllers\Api\ApiKey\ApiKeyController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\OtpController;
use App\Http\Controllers\Api\Contract\ContractController;
use App\Http\Controllers\Api\Influencer\InfluencerController;
use App\Http\Controllers\Api\Project\ProjectController;
use App\Http\Controllers\Api\Quota\QuotaController;
use App\Http\Controllers\Api\Service\ServiceController;
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
        // OTP routes - telefon doğrulaması gerektirmez
        Route::post('otp/send', [OtpController::class, 'send']);
        Route::post('otp/verify', [OtpController::class, 'verify']);

        // Contract routes - telefon doğrulaması gerektirmez (sözleşmeleri okumak ve kabul etmek için)
        Route::get('contracts/active', [ContractController::class, 'active']);
        Route::get('contracts/{contract}', [ContractController::class, 'show']);
        Route::post('contracts/{contract}/accept', [ContractController::class, 'accept']);
        Route::get('contracts/my/acceptances', [ContractController::class, 'myAcceptances']);

        // Root kullanıcılar için sözleşme CRUD
        Route::middleware(['root.user'])->group(function () {
            Route::get('contracts', [ContractController::class, 'index']);
            Route::post('contracts', [ContractController::class, 'store']);
            Route::put('contracts/{contract}', [ContractController::class, 'update']);
            Route::delete('contracts/{contract}', [ContractController::class, 'destroy']);
        });

        Route::middleware([\App\Http\Middleware\EnsureJwtClaimsMatch::class])->group(function () {
            Route::get('me', [AuthController::class, 'me']);

            // Telefon ve sözleşme doğrulaması gerektiren route'lar
            Route::middleware(['phone.verified', 'contract.accepted'])->group(function () {
                // Service routes - aktif servisleri görme ve API key ekleme için
                Route::get('services/active', [ServiceController::class, 'active']);
                Route::get('services/{service}', [ServiceController::class, 'show']);

                // Root kullanıcılar için servis CRUD
                Route::middleware(['root.user'])->group(function () {
                    Route::get('services', [ServiceController::class, 'index']);
                    Route::post('services', [ServiceController::class, 'store']);
                    Route::put('services/{service}', [ServiceController::class, 'update']);
                    Route::delete('services/{service}', [ServiceController::class, 'destroy']);
                });

                // API Key routes - API key ekleme, güncelleme, silme
                Route::get('api-keys', [ApiKeyController::class, 'index']);
                Route::get('api-keys/{apiKey}', [ApiKeyController::class, 'show']);
                Route::post('api-keys', [ApiKeyController::class, 'store']);
                Route::put('api-keys/{apiKey}', [ApiKeyController::class, 'update']);
                Route::delete('api-keys/{apiKey}', [ApiKeyController::class, 'destroy']);

                // API key kontrolü gerektiren route'lar
                Route::middleware(['api.keys'])->group(function () {
                    Route::get('user/request-history', [\App\Http\Controllers\Api\RequestLogController::class, 'index']);

                    Route::get('credits/balance', [\App\Http\Controllers\Api\CreditController::class, 'balance']);
                    Route::get('credits/history', [\App\Http\Controllers\Api\CreditController::class, 'history']);

                    Route::post('payments/checkout', [\App\Http\Controllers\Api\PaymentController::class, 'checkout']);

                    Route::apiResource('projects', ProjectController::class);
                    Route::post('projects/{project}/restore', [ProjectController::class, 'restore']);
                    Route::delete('projects/{project}/force', [ProjectController::class, 'forceDelete']);

                    // Influencer routes
                    Route::get('projects/{projectId}/influencer', [InfluencerController::class, 'show']);
                    Route::post('projects/{projectId}/influencer', [InfluencerController::class, 'store']);
                    Route::put('projects/{projectId}/influencer', [InfluencerController::class, 'update']);
                    Route::delete('projects/{projectId}/influencer', [InfluencerController::class, 'destroy']);

                    Route::get('quota', [QuotaController::class, 'show']);
                    Route::put('quota', [QuotaController::class, 'update']);
                });
            });

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
