<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DataTransferObjects\ForgotPasswordDTO;
use App\Domain\User\Models\User;
use App\Services\OtpService;
use Illuminate\Validation\ValidationException;

class ForgotPasswordAction
{
    public function __construct(protected OtpService $otpService) {}

    public function execute(ForgotPasswordDTO $data): void
    {
        $user = User::where('phone', $data->phone)->first();

        if (! $user) {
            // To prevent user enumeration, we might want to return success even if user not found, 
            // but for this specific flow it's common to validate phone existence first 
            // OR simply do nothing. Let's send OTP only if user exists and return success.
            // If we throw error, it reveals user existence.
            // Let's decide to throw error for better UX in this specific app, 
            // or just return and do nothing (silent fail).
            // Given the user prompt, let's validate phone exists via request validation or here.
            throw ValidationException::withMessages([
                'phone' => ['We could not find a user with that phone number.'],
            ]);
        }

        $this->otpService->sendOtp($user->phone);
    }
}
