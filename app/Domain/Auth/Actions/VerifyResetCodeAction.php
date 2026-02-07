<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DataTransferObjects\VerifyResetCodeDTO;
use App\Services\OtpService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VerifyResetCodeAction
{
    public function __construct(protected OtpService $otpService) {}

    public function execute(VerifyResetCodeDTO $data): string
    {
        if (! $this->otpService->verifyOtp($data->phone, $data->verification_code)) {
            throw ValidationException::withMessages([
                'verification_code' => ['Invalid or expired verification code.'],
            ]);
        }

        // Generate a secure reset token
        $resetToken = Str::random(60);
        
        // Cache it for a short time (e.g., 15 minutes) associated with the phone
        Cache::put("password_reset_{$data->phone}", $resetToken, 900);

        return $resetToken;
    }
}
