<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'error' => true,
                'errorCode' => 1001,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Telefon numarası boş kontrolü
        if (empty($user->phone)) {
            return response()->json([
                'error' => true,
                'errorCode' => 1001,
                'message' => 'Phone number is required',
            ], 403);
        }

        // Telefon doğrulama kontrolü
        if (is_null($user->phone_verified_at)) {
            return response()->json([
                'error' => true,
                'errorCode' => 1001,
                'message' => 'Phone number must be verified',
            ], 403);
        }

        return $next($request);
    }
}
