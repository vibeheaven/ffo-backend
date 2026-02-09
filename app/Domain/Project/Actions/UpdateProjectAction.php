<?php

namespace App\Domain\Project\Actions;

use App\Domain\Project\DataTransferObjects\UpdateProjectDTO;
use App\Domain\Project\Models\Project;

class UpdateProjectAction
{
    public function execute(Project $project, UpdateProjectDTO $dto): Project
    {
        $project->update($dto->toArray());
        
        return $project->fresh();
    }
}
