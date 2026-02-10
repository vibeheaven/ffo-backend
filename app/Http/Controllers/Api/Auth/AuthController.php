<?php

namespace App\Http\Controllers\Api\Auth;

use App\Domain\Auth\Actions\ForgotPasswordAction;
use App\Domain\Auth\Actions\LoginAction;
use App\Domain\Auth\Actions\RegisterAction;
use App\Domain\Auth\Actions\ResetPasswordAction;
use App\Domain\Auth\Actions\VerifyResetCodeAction;
use App\Domain\Project\Actions\CreateDefaultProjectAction;
use App\Domain\Auth\DataTransferObjects\ForgotPasswordDTO;
use App\Domain\Auth\DataTransferObjects\LoginDTO;
use App\Domain\Auth\DataTransferObjects\RegisterDTO;
use App\Domain\Auth\DataTransferObjects\ResetPasswordDTO;
use App\Domain\Auth\DataTransferObjects\VerifyResetCodeDTO;
use App\Domain\Project\Models\Project;
use App\Domain\User\DataTransferObjects\UserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyResetCodeRequest;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;


class AuthController extends Controller
{

   
    public function login(
        LoginRequest $request,
        CreateDefaultProjectAction $createDefaultProjectAction,
        OtpService $otpService
    ): JsonResponse {
        $credentials = $request->only('email', 'password');

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $user = auth('api')->user();

        // Eğer proje yoksa default proje oluştur
        $createDefaultProjectAction->execute($user);
        
        // Telefon numarası varsa ve doğrulanmamışsa OTP gönder
        if (!empty($user->phone) && is_null($user->phone_verified_at)) {
            try {
                $otpService->sendOtp($user->phone);
            } catch (\Exception $e) {
                // OTP gönderimi başarısız olsa bile login devam eder
            }
        }
        
        return $this->respondWithToken($token, $user);
    }
    
   
    public function register(\App\Http\Requests\Auth\RegisterRequest $request, \App\Domain\Auth\Actions\RegisterAction $action, OtpService $otpService): JsonResponse
    {
        $user = $action->execute(\App\Domain\Auth\DataTransferObjects\RegisterDTO::fromRequest($request->validated(), $request->file('profile_photo')));
        
        $token = auth('api')->login($user);
        
        // Telefon numarası varsa ve doğrulanmamışsa OTP gönder
        if (!empty($user->phone) && is_null($user->phone_verified_at)) {
            try {
                $otpService->sendOtp($user->phone);
            } catch (\Exception $e) {
                // OTP gönderimi başarısız olsa bile kayıt devam eder
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => UserDTO::fromModel($user)->toArray(),
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                ],
            ],
            'message' => 'User registered successfully.',
        ], 201);
    }

    
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

  
    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth('api')->refresh(), auth('api')->user());
    }

    
    public function me(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        $projects = Project::where('user_id', $user->id)->withTrashed()->pluck('id')->toArray();

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => UserDTO::fromModel($user)->toArray(),
                'projects' => $projects,
            ],
        ]);
    }


    protected function respondWithToken($token, $user): JsonResponse
    {
        $phoneVerificationRequired = !empty($user->phone) && is_null($user->phone_verified_at);
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => UserDTO::fromModel($user)->toBasicArray(),
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                ],
                'phone_verification_required' => $phoneVerificationRequired,
            ],
        ]);
    }


    
    public function forgotPassword(ForgotPasswordRequest $request, ForgotPasswordAction $action): JsonResponse
    {
        $action->execute(ForgotPasswordDTO::fromRequest($request->validated()));

        return response()->json([
            'status' => 'success',
            'message' => 'If a user with that phone number exists, an OTP has been sent.',
        ]);
    }

   
    public function verifyResetCode(VerifyResetCodeRequest $request, VerifyResetCodeAction $action): JsonResponse
    {
        $token = $action->execute(VerifyResetCodeDTO::fromRequest($request->validated()));

        return response()->json([
            'status' => 'success',
            'data' => [
                'reset_token' => $token,
            ],
            'message' => 'Code verified. You can now reset your password.',
        ]);
    }

    
    public function resetPassword(ResetPasswordRequest $request, ResetPasswordAction $action): JsonResponse
    {
        $action->execute(ResetPasswordDTO::fromRequest($request->validated()));

        return response()->json([
            'status' => 'success',
            'message' => 'Password has been reset successfully.',
        ]);
    }
}
