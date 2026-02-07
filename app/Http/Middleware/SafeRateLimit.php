<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;
use Exception;

class SafeRateLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $limit = 60, $time = 1): Response
    {
        try {
            // Use Redis throttle
            $ip = $request->ip();
            $key = 'rate_limit:' . $ip;

            // Simple Redis checking or Laravel's RedisLimiter
            // We use raw Redis commands here for simplicity and control or Laravel's Limiter facade logic
            // Let's use Laravel's built-in RateLimiter via Redis facade to keep it atomic
            
            // Allow $limit requests per $time minutes
            $executed = Redis::throttle($key)
                ->allow($limit)
                ->every($time * 60)
                ->then(function () {
                    return true;
                }, function () {
                    return false;
                });

            if (! $executed) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Too many requests. Please try again later.',
                ], 429);
            }

        } catch (Exception $e) {
            // Redis is down or connection failed
            Log::critical('Redis connection failed in SafeRateLimit: ' . $e->getMessage());

            // SYSTEM PROTECTED MODE
            // Fail Closed to protect the system from overload when rate limiting is missing
            return response()->json([
                'status' => 'error',
                'message' => 'System Protected Mode: Service temporarily unavailable due to infrastructure stability checks.',
                'error_code' => 'SPM_REDIS_FAIL'
            ], 503);
        }

        return $next($request);
    }
}
