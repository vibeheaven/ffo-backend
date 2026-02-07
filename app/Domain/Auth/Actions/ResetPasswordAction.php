<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DataTransferObjects\ResetPasswordDTO;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ResetPasswordAction
{
    public function execute(ResetPasswordDTO $data): void
    {
        $storedToken = Cache::get("password_reset_{$data->phone}");

        if (! $storedToken || $storedToken !== $data->reset_token) {
            throw ValidationException::withMessages([
                'reset_token' => ['Invalid or expired password reset token.'],
            ]);
        }

        $user = User::where('phone', $data->phone)->firstOrFail();

        $user->forceFill([
            'password' => Hash::make($data->password),
        ])->save();

        // Invalidate the token
        Cache::forget("password_reset_{$data->phone}");
    }
}
