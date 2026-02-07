<?php

namespace App\Http\Controllers\Api\Auth;

use App\Domain\Auth\Actions\ForgotPasswordAction;
use App\Domain\Auth\Actions\LoginAction;
use App\Domain\Auth\Actions\RegisterAction;
use App\Domain\Auth\Actions\ResetPasswordAction;
use App\Domain\Auth\Actions\VerifyResetCodeAction;
use App\Domain\Auth\DataTransferObjects\ForgotPasswordDTO;
use App\Domain\Auth\DataTransferObjects\LoginDTO;
use App\Domain\Auth\DataTransferObjects\RegisterDTO;
use App\Domain\Auth\DataTransferObjects\ResetPasswordDTO;
use App\Domain\Auth\DataTransferObjects\VerifyResetCodeDTO;
use App\Domain\User\DataTransferObjects\UserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyResetCodeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Aethron Auth API",
 *      description="Authentication System API",
 * )
 *
 * @OA\SecurityScheme(
 *      securityScheme="bearerAuth",
 *      type="http",
 *      scheme="bearer"
 * )
 */
class AuthController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="User Login",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="authorization", type="object",
     *                     @OA\Property(property="token", type="string"),
     *                     @OA\Property(property="type", type="string", example="bearer"),
     *                     @OA\Property(property="expires_in", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $user = auth('api')->user();

        // Check if registration is complete if needed, or return step
        // We can use UserDTO if we want filtered fields
        
        return $this->respondWithToken($token, $user);
    }
    
    // ... Register steps remain mostly same but return proper token if they perform auto-login ...
    // Note: To auto-login after register, we need to generate token via auth('api')->login($user)
    
    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     summary="Register New User",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name","email","password","password_confirmation"},
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="password", type="string", format="password", example="password"),
     *                 @OA\Property(property="password_confirmation", type="string", format="password", example="password"),
     *                 @OA\Property(property="gender", type="string", enum={"male", "female", "other"}),
     *                 @OA\Property(property="birthday", type="string", format="date", example="1990-01-01"),
     *                 @OA\Property(property="location", type="string", example="New York, USA"),
     *                 @OA\Property(property="language", type="string", example="en"),
     *                 @OA\Property(property="profile_photo", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="authorization", type="object")
     *             ),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function register(\App\Http\Requests\Auth\RegisterRequest $request, \App\Domain\Auth\Actions\RegisterAction $action): JsonResponse
    {
        $user = $action->execute(\App\Domain\Auth\DataTransferObjects\RegisterDTO::fromRequest($request->validated(), $request->file('profile_photo')));
        
        // Generate JWT for the new user
        $token = auth('api')->login($user);        

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

    // Register 2, 3, 4 use $request->user() which now resolves via JWT guard automatically.
    // They are protected by auth:api middleware in routes.

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Logout (Revoke Token)",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged out",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/refresh",
     *     summary="Refresh Token (Rotation)",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="New Token",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="authorization", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth('api')->refresh(), auth('api')->user());
    }

    /**
     * @OA\Get(
     *     path="/api/auth/me",
     *     summary="Get Authenticated User",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User details",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => UserDTO::fromModel(auth('api')->user())->toArray(),
            ],
        ]);
    }

    // ... Forgot Password methods remain same ...

    protected function respondWithToken($token, $user): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => UserDTO::fromModel($user)->toBasicArray(),
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                ],
            ],
        ]);
    }


    /**
     * @OA\Post(
     *     path="/api/auth/forgot-password",
     *     summary="Request Password Reset OTP",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone"},
     *             @OA\Property(property="phone", type="string", example="+1234567890")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP Sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function forgotPassword(ForgotPasswordRequest $request, ForgotPasswordAction $action): JsonResponse
    {
        $action->execute(ForgotPasswordDTO::fromRequest($request->validated()));

        return response()->json([
            'status' => 'success',
            'message' => 'If a user with that phone number exists, an OTP has been sent.',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/forgot-password/verify",
     *     summary="Verify Password Reset OTP",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone", "verification_code"},
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="verification_code", type="string", example="123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP Verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="reset_token", type="string", description="Use this token to reset password")
     *             ),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/auth/reset-password",
     *     summary="Reset Password",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone", "reset_token", "password", "password_confirmation"},
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="reset_token", type="string"),
     *             @OA\Property(property="password", type="string", format="password", example="new_password"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="new_password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password Reset Successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function resetPassword(ResetPasswordRequest $request, ResetPasswordAction $action): JsonResponse
    {
        $action->execute(ResetPasswordDTO::fromRequest($request->validated()));

        return response()->json([
            'status' => 'success',
            'message' => 'Password has been reset successfully.',
        ]);
    }
}
