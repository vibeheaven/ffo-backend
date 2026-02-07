<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OtpService
{
    protected const TTL = 300; // 5 minutes

    public function sendOtp(string $phone): void
    {
        $code = (string) random_int(100000, 999999);

        // Store code in cache
        Cache::put("otp_{$phone}", $code, self::TTL);

        // Send via Webhook
        try {
            Http::post('https://xdevsp5a.rpcld.net/webhook/aea6a809-d4b9-4c80-8341-49dad0139ade', [
                'phone' => $phone,
                'code' => $code,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send OTP webhook: {$e->getMessage()}");
            // We might choose to rethrow or just log.
            // For now, logging. In production, this should likely fail the request.
        }
    }

    public function verifyOtp(string $phone, string $code): bool
    {
        $storedCode = Cache::get("otp_{$phone}");

        if ($storedCode && $storedCode === $code) {
            Cache::forget("otp_{$phone}");
            return true;
        }

        return false;
    }
}
