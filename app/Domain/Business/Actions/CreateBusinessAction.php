<?php

namespace App\Domain\Business\Actions;

use App\Domain\Business\DataTransferObjects\CreateBusinessDTO;
use App\Domain\Business\Models\Business;
use App\Domain\Project\Models\Project;

class CreateBusinessAction
{
    public function execute(Project $project, CreateBusinessDTO $dto): Business
    {
        return Business::create([
            'project_id' => $project->id,
            ...$dto->toArray(),
        ]);
    }
}
