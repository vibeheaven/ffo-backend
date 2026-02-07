<?php

namespace App\Http\Middleware;

use App\Domain\Logging\Models\RequestLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogHttpActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $this->log($request, $response, $startTime);

        return $response;
    }

    protected function log(Request $request, Response $response, float $startTime): void
    {
        try {
            $duration = (int) ((microtime(true) - $startTime) * 1000);

            // Filter out sensitive data from payload
            $requestPayload = $request->except(['password', 'password_confirmation']);

            // Get response content (be careful with large responses)
            $responseContent = null;
            if ($response instanceof \Illuminate\Http\JsonResponse) {
               $responseContent = $response->getData(true);
            }

            RequestLog::create([
                'user_id' => $request->user('sanctum')?->id, // Attempt to get user if auth
                'method' => $request->method(),
                'endpoint' => $request->fullUrl(),
                'status_code' => $response->getStatusCode(),
                'ip_address' => $request->ip(),
                'duration_ms' => $duration,
                'request_payload' => $requestPayload,
                'response_payload' => $responseContent,
            ]);
        } catch (\Exception $e) {
            // Do not fail the request if logging fails
            // Log::error('Logging failed: ' . $e->getMessage());
        }
    }
}
