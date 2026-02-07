<?php

namespace App\Domain\Credits\Actions;

use App\Domain\Credits\Models\CreditTransaction;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\DB;

class AddCreditsAction
{
    public function execute(User $user, float $amount, string $description, ?string $referenceId = null): void
    {
        DB::transaction(function () use ($user, $amount, $description, $referenceId) {
            // Lock user row to ensure consistency if multiple transactions happen at once
            $user = User::where('id', $user->id)->lockForUpdate()->first();

            $user->increment('credits', $amount);

            CreditTransaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'type' => 'purchase', // or bonus, etc. - could be passed as arg
                'description' => $description,
                'reference_id' => $referenceId,
            ]);
        });
    }
}
