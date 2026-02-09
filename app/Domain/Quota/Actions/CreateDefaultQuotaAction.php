<?php

namespace App\Domain\Quota\Actions;

use App\Domain\Quota\Models\ProjectQuota;
use App\Domain\User\Models\User;

class CreateDefaultQuotaAction
{
    public function execute(User $user): ProjectQuota
    {
        // Eğer kullanıcının kotası varsa oluşturma
        $quota = ProjectQuota::where('user_id', $user->id)->first();

        if ($quota) {
            return $quota;
        }

        // Default quota ile oluştur
        return ProjectQuota::create([
            'user_id' => $user->id,
            'quota' => 1,
        ]);
    }
}
