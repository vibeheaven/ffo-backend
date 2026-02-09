<?php

namespace App\Domain\Business\Actions;

use App\Domain\Business\DataTransferObjects\UpdateBusinessDTO;
use App\Domain\Business\Models\Business;

class UpdateBusinessAction
{
    public function execute(Business $business, UpdateBusinessDTO $dto): Business
    {
        $business->update($dto->toArray());
        
        return $business->fresh();
    }
}
