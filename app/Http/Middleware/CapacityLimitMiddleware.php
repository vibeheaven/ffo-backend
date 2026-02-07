<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class CapacityLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Limit: 50 concurrent requests
            // Lock acquisition wait: 10 seconds
            return Redis::funnel('api_capacity')
                ->limit(50)
                ->block(10)
                ->then(function () use ($next, $request) {
                    return $next($request);
                }, function () {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Server is currently experiencing high traffic. Please try again shortly.',
                    ], 429); 
                });
        } catch (\Exception $e) {
            // If Redis fails, protect the system
            \Illuminate\Support\Facades\Log::critical('Redis failed in CapacityLimitMiddleware: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'System Protected Mode: Capacity check failed.',
                'error_code' => 'SPM_CAPACITY_FAIL'
            ], 503);
        }
    }
}
