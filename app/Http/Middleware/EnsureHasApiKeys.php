<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasApiKeys
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
                'errorCode' => 1003,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Root kullanıcılar için API key kontrolü yapma
        if ($user->isRootUser()) {
            return $next($request);
        }

        // API key var mı kontrol et
        if (!$user->hasApiKeys()) {
            return response()->json([
                'error' => true,
                'errorCode' => 1003,
                'message' => 'You must add at least one API key to continue.',
            ], 403);
        }

        return $next($request);
    }
}
