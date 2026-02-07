<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Exception;

class EnsureJwtClaimsMatch
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Get the payload from the token
            $payload = JWTAuth::parseToken()->getPayload();
            $tokenHash = $payload->get('u_hash');

            // Calculate current hash
            $currentHash = hash('sha256', $request->ip() . $request->userAgent());

            if (! hash_equals($tokenHash, $currentHash)) {
                // Determine what mismatched for logging (optional)
                // Invalidate token? Maybe not automatically to prevent DoS, but definitely block request.
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token mismatch: Potential hijacking attempt detected.',
                ], 401);
            }
        } catch (Exception $e) {
            // Token invalid or not found - let the guard handle it or return 401
            return response()->json(['status' => 'error', 'message' => 'Token invalid'], 401);
        }

        return $next($request);
    }
}
