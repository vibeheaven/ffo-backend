<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OtpController extends Controller
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    /**
     * OTP kodu gönder
     */
    public function send(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'error' => true,
                'errorCode' => 401,
                'message' => 'Unauthenticated',
            ], 401);
        }

        if (empty($user->phone)) {
            $validated = $request->validate([
                'phone' => 'required|string',
            ]);

            // Kullanıcıya yeni telefonu güncelle
            $user->phone = $validated['phone'];
            $user->save();
        }

        $phoneToSend = $user->phone ?? $request->input('phone');

        $this->otpService->sendOtp($phoneToSend);

        return response()->json([
            'status' => 'success',
            'message' => 'OTP code has been sent to your phone number.',
        ]);
    }

    /**
     * OTP kodunu doğrula
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'error' => true,
                'errorCode' => 401,
                'message' => 'Unauthenticated',
            ], 401);
        }

        if (empty($user->phone)) {
            return response()->json([
                'error' => true,
                'errorCode' => 400,
                'message' => 'Phone number is not set.',
            ], 400);
        }

        $isValid = $this->otpService->verifyOtp($user->phone, $request->code);

        if (!$isValid) {
            return response()->json([
                'error' => true,
                'errorCode' => 400,
                'message' => 'Invalid or expired OTP code.',
            ], 400);
        }

        // Telefon numarasını doğrulanmış olarak işaretle
        $user->phone_verified_at = now();
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Phone number verified successfully.',
        ]);
    }
}
