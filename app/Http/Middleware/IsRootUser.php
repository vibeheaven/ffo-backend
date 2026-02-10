<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsRootUser
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
                'errorCode' => 403,
                'message' => 'Unauthenticated',
            ], 401);
        }

        if (!$user->isRootUser()) {
            return response()->json([
                'error' => true,
                'errorCode' => 403,
                'message' => 'Unauthorized. Only root users can perform this action.',
            ], 403);
        }

        return $next($request);
    }
}
