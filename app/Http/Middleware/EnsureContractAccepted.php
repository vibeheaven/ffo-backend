<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureContractAccepted
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
                'errorCode' => 1002,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Root kullanıcılar için sözleşme kontrolü yapma
        if ($user->isRootUser()) {
            return $next($request);
        }

        // Aktif sözleşmeleri kabul etmiş mi kontrol et
        if (!$user->hasAcceptedActiveContracts()) {
            return response()->json([
                'error' => true,
                'errorCode' => 1002,
                'message' => 'You must accept all active contracts to continue.',
            ], 403);
        }

        return $next($request);
    }
}
