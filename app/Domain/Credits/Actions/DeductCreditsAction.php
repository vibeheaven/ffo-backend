<?php

namespace App\Domain\Credits\Actions;

use App\Domain\Credits\Models\CreditTransaction;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeductCreditsAction
{
    public function execute(User $user, float $amount, string $description, ?string $referenceId = null): void
    {
        DB::transaction(function () use ($user, $amount, $description, $referenceId) {
            $user = User::where('id', $user->id)->lockForUpdate()->first();

            if ($user->credits < $amount) {
                throw ValidationException::withMessages([
                    'credits' => ['Insufficient credits.'],
                ]);
            }

            $user->decrement('credits', $amount);

            CreditTransaction::create([
                'user_id' => $user->id,
                'amount' => -$amount, // Negative for deduction
                'type' => 'usage',
                'description' => $description,
                'reference_id' => $referenceId,
            ]);
        });
    }
}
