<?php

namespace App\Domain\Quota\Actions;

use App\Domain\Quota\DataTransferObjects\UpdateQuotaDTO;
use App\Domain\Quota\Models\ProjectQuota;

class UpdateQuotaAction
{
    public function execute(ProjectQuota $quota, UpdateQuotaDTO $dto): ProjectQuota
    {
        $quota->update([
            'quota' => $dto->quota,
        ]);

        return $quota->fresh();
    }
}
